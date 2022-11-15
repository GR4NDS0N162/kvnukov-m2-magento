<?php

declare(strict_types=1);

namespace Oggetto\News\Controller\Adminhtml\News;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Validation\ValidationException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Oggetto\News\Api\Data\NewsInterface;
use Oggetto\News\Api\NewsRepositoryInterface;
use Oggetto\News\Controller\Adminhtml\News as NewsAction;
use Oggetto\News\Model\News;
use Oggetto\News\Model\NewsFactory;
use Psr\Log\LoggerInterface;

class Save extends NewsAction implements HttpPostActionInterface
{
    /**
     * @var NewsFactory
     */
    protected $newsFactory;
    /**
     * @var NewsRepositoryInterface
     */
    protected $newsRepository;
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param Context $context
     * @param NewsFactory $newsFactory
     * @param NewsRepositoryInterface $newsRepository
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $fileSystem
     * @param LoggerInterface $logger
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        NewsFactory $newsFactory,
        NewsRepositoryInterface $newsRepository,
        UploaderFactory $uploaderFactory,
        FileSystem $fileSystem,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->newsFactory = $newsFactory;
        $this->newsRepository = $newsRepository;
        $this->uploaderFactory = $uploaderFactory;
        $this->logger = $logger;
        $this->mediaDirectory = $fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data[NewsInterface::ID])) {
                $data[NewsInterface::ID] = null;
            }

            $model = $this->newsFactory->create();

            $id = $this->getRequest()->getParam(NewsInterface::ID);
            if ($id) {
                try {
                    $model = $this->newsRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This news no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            if (isset($data[NewsInterface::IMAGE]) && count($data[NewsInterface::IMAGE])) {
                try {
                    $imageId = $data[NewsInterface::IMAGE][0];
                    if (!file_exists($imageId['tmp_name'])) {
                        $imageId['tmp_name'] = $imageId['path'] . '/' . $imageId['file'];
                    }

                    $fileUploader = $this->uploaderFactory->create(['fileId' => $imageId]);
                    $fileUploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                    $fileUploader->setAllowRenameFiles(true);
                    $fileUploader->setAllowCreateFolders(true);
                    $fileUploader->validateFile();

                    $info = $fileUploader->save($this->mediaDirectory->getAbsolutePath('imageUploader/images'));
                    $data[NewsInterface::IMAGE] = $this->mediaDirectory->getRelativePath(
                        'imageUploader/images',
                    ) . '/' . $info['file'];
                } catch (ValidationException) {
                    throw new LocalizedException(__(
                        'Image extension is not supported. Only extensions allowed are jpg, jpeg and png',
                    ));
                } catch (\Exception) {
                    throw new LocalizedException(__('Image is required'));
                }
            }

            $model->setData($data);

            try {
                $this->newsRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the news.'));
                return $this->processNewsReturn($model, $data, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the news.'));
            }

            return $resultRedirect->setPath('*/*/edit', [NewsInterface::ID => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process and set the block return
     *
     * @param News $model
     * @param array $data
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws CouldNotSaveException
     */
    private function processNewsReturn($model, $data, $resultRedirect)
    {
        $redirect = $data['back'] ?? 'close';

        if ($redirect === 'continue') {
            $resultRedirect->setPath('*/*/edit', [NewsInterface::ID => $model->getId()]);
        } elseif ($redirect === 'close') {
            $resultRedirect->setPath('*/*/');
        } elseif ($redirect === 'duplicate') {
            $duplicateModel = $this->newsFactory->create(['data' => $data]);
            $duplicateModel->setId(null);
            $duplicateModel->setStatus(News::STATUS_DISABLED);
            $this->newsRepository->save($duplicateModel);
            $id = $duplicateModel->getId();
            $this->messageManager->addSuccessMessage(__('You duplicated the news.'));
            $resultRedirect->setPath('*/*/edit', [NewsInterface::ID => $id]);
        }
        return $resultRedirect;
    }
}
