<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Ui\DataProvider\Queue\Orders\FieldStrategy;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use MageWorx\DeliveryDate\Ui\DataProvider\Queue\OrdersDataProvider;

class AddOrderFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @inheritdoc
     * @throws \Zend_Db_Select_Exception
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        // join table
        $alias = $alias ?? '';
        $this->_addField($collection, $field, $alias);
    }

    /**
     * Check is sales_order table already has been joined to the collection
     *
     * @param Collection $collection
     * @return bool
     */
    protected function isOrdersTableJoined(Collection $collection): bool
    {
        $select   = $collection->getSelect();
        $fromPart = $select->getPart('from');

        return !empty($fromPart[OrdersDataProvider::SALES_ORDER_TABLE_ALIAS]);
    }

    /**
     * @param Collection $collection
     * @param string $field
     * @param string $alias
     * @return void
     * @throws \Zend_Db_Select_Exception
     */
    protected function _addField(Collection $collection, string $field, string $alias): void
    {
        /** @var \Zend_Db_Select $select */
        $select = $collection->getSelect();
        if ($this->isOrdersTableJoined($collection)) {
            $select->columns([$field], OrdersDataProvider::SALES_ORDER_TABLE_ALIAS);
        } else {
            $select->joinLeft(
                [OrdersDataProvider::SALES_ORDER_TABLE_ALIAS => $collection->getResource()->getTable('sales_order')],
                'main_table.parent_id = ' . OrdersDataProvider::SALES_ORDER_TABLE_ALIAS . '.entity_id',
                $field
            );
        }
    }
}
