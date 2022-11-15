<?php

declare(strict_types=1);

namespace Oggetto\News\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Oggetto\News\Api\Data\NewsInterface;
use Oggetto\News\Api\NewsRepositoryInterface;
use Oggetto\News\Model\ResourceModel\News as ResourceNews;
use Oggetto\News\Model\ResourceModel\News\CollectionFactory;

class NewsRepository implements NewsRepositoryInterface
{
    /**
     * @var ResourceNews
     */
    protected $resource;
    /**
     * @var NewsFactory
     */
    protected $newsFactory;
    /**
     * @var CollectionFactory
     */
    protected $newsCollectionFactory;

    /**
     * @param ResourceNews $resource
     * @param NewsFactory $newsFactory
     * @param CollectionFactory $newsCollectionFactory
     */
    public function __construct(
        ResourceNews $resource,
        NewsFactory $newsFactory,
        CollectionFactory $newsCollectionFactory,
    ) {
        $this->resource = $resource;
        $this->newsFactory = $newsFactory;
        $this->newsCollectionFactory = $newsCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById($newsId)
    {
        $news = $this->newsFactory->create();
        $this->resource->load($news, $newsId);
        if (!$news->getId()) {
            throw new NoSuchEntityException(__('The news with the "%1" ID doesn\'t exist.', $newsId));
        }
        return $news;
    }

    /**
     * @inheritDoc
     */
    public function save(NewsInterface $news)
    {
        try {
            $this->resource->save($news);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $news;
    }

    /**
     * @inheritDoc
     */
    public function getList()
    {
        return $this->newsCollectionFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function delete(NewsInterface $news)
    {
        try {
            $this->resource->delete($news);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($newsId)
    {
        return $this->delete($this->getById($newsId));
    }
}
