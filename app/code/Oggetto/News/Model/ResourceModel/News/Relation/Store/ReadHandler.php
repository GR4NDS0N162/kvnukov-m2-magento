<?php

declare(strict_types=1);

namespace Oggetto\News\Model\ResourceModel\News\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Oggetto\News\Model\ResourceModel\News;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var News
     */
    protected News $resourceNews;

    /**
     * @param News $resourceNews
     */
    public function __construct(
        News $resourceNews,
    ) {
        $this->resourceNews = $resourceNews;
    }

    /**
     * @inheritDoc
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceNews->lookupStoreIds((int) $entity->getId());
            $entity->setData('stores', $stores);
        }
        return $entity;
    }
}
