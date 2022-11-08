<?php

namespace Oggetto\News\Ui\Component;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Model\AbstractModel;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @inheritDoc
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            if ($item instanceof AbstractModel) {
                $arrItems['items'][] = $item->getData();
            }
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}
