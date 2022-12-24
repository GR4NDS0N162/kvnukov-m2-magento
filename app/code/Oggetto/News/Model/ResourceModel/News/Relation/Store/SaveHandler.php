<?php

declare(strict_types=1);

namespace Oggetto\News\Model\ResourceModel\News\Relation\Store;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Oggetto\News\Api\Data\NewsInterface;
use Oggetto\News\Model\ResourceModel\News;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected MetadataPool $metadataPool;

    /**
     * @var News
     */
    protected News $resourceNews;

    /**
     * @param MetadataPool $metadataPool
     * @param News         $resourceNews
     */
    public function __construct(
        MetadataPool $metadataPool,
        News $resourceNews,
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceNews = $resourceNews;
    }

    /**
     * @inheritDoc
     */
    public function execute($news, $arguments = [])
    {
        if (!($news instanceof NewsInterface)) {
            return $news;
        }

        $connection = $this->resourceNews->getConnection();
        $newsId = $news->getId();

        $oldStores = $this->resourceNews->lookupStoreIds((int) $newsId);
        $newStores = (array) $news->getData(NewsInterface::STORES);

        $table = $this->resourceNews->getTable(News::NEWS_STORE_TABLE_NAME);

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = $connection->prepareSqlCondition(News::NEWS_ID, ['eq' => $newsId]);
            $where .= ' AND ' . $connection->prepareSqlCondition(News::STORE_ID, ['in' => $delete]);
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    News::NEWS_ID  => (int) $newsId,
                    News::STORE_ID => (int) $storeId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $news;
    }
}
