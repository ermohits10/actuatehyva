<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\Grid;

use MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\Collection as AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|Collection
     */
    public function loadWithFilter($printQuery = false, $logQuery = false)
    {
        $this->joinStoreGroupTable();
        $this->getSelect()->columns(
            [
                'store_ids'          => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `store_group`.`store_id`)'),
                'customer_group_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `store_group`.`customer_group_id`)')
            ]
        );
        $this->getSelect()->distinct(true);
        $this->getSelect()->group('entity_id');
        $this->storeGroupAddedFlag = true;

        return parent::loadWithFilter($printQuery, $logQuery);
    }
}
