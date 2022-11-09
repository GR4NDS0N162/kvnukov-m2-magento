<?php

namespace Oggetto\News\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Oggetto\News\Api\Data\NewsInterface;
use Oggetto\News\Model\ResourceModel\News\Collection;

interface NewsRepositoryInterface
{
    /**
     * Receive news by id
     *
     * @param int $newsId
     * @return NewsInterface
     * @throws LocalizedException
     */
    public function getById($newsId);

    /**
     * Save news
     *
     * @param NewsInterface $news
     * @return NewsInterface
     * @throws LocalizedException
     */
    public function save($news);

    /**
     * Retrieve news collection
     *
     * @return Collection
     */
    public function getList();

    /**
     * Delete news
     *
     * @param NewsInterface $news
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete($news);

    /**
     * Delete news by ID
     *
     * @param string $newsId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($newsId);
}
