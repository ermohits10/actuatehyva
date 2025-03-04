<?php

namespace Actuate\MirasvitSearchUltimate\Model\ResourceModel;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Mirasvit\Search\Model\ConfigProvider;

class SearchResultApplier extends \Mirasvit\Search\Model\ResourceModel\SearchResultApplier
{
    private $collection;

    private $searchResult;

    private $size;

    private $currentPage;

    private $configProvider;

    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        int $size,
        int $currentPage,
        ConfigProvider $configProvider
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->size = $size;
        $this->currentPage = $currentPage;
        $this->configProvider = $configProvider;
        parent::__construct($collection, $searchResult, $size, $currentPage, $configProvider);
    }

    public function apply(): void
    {
        $items = $this->searchResult->getItems();
        if (empty($items)) {
            $this->collection->getSelect()->where('NULL');
            return;
        }

        $ids = [];
        foreach ($items as $item) {
            $ids[] = (int)$item->getId();
        }

        $this->collection->getSelect()
            ->where('e.entity_id IN (?)', $ids)
            ->reset(\Magento\Framework\DB\Select::ORDER);
        $sortOrder = $this->searchResult->getSearchCriteria()->getSortOrders();

        if (!empty($sortOrder['price']) && $this->collection->getLimitationFilters()->isUsingPriceIndex()) {
            $sortDirection = $sortOrder['price'];
            $this->collection->getSelect()
                ->order(new \Zend_Db_Expr("price_index.min_price = 0, price_index.min_price"), $sortDirection);
        } else {
            $orderList = join(',', $ids);
            $this->collection->getSelect()->order(new \Zend_Db_Expr("FIELD(e.entity_id,$orderList)"));
            if (!empty($sortOrder['position'])) {
                $this->collection->getSelect()->order('e.entity_id', 'ASC');
            }
        }

        $this->collection->getSelect()->limit($this->size, $this->getOffset($this->currentPage, $this->size));
    }

    private function getOffset(int $pageNumber, int $pageSize): int
    {
        return ($pageNumber - 1) * $pageSize;
    }
}
