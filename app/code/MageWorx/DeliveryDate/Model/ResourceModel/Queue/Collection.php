<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class Collection extends AbstractCollection
{
    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \MageWorx\DeliveryDate\Model\Queue::class,
            \MageWorx\DeliveryDate\Model\ResourceModel\Queue::class
        );
    }

    /**
     * Get order address ids as array by delivery option id
     *
     * @param int $deliveryOptionId
     * @return array
     */
    public function getOrderAddressIdsByDeliveryOptionId($deliveryOptionId)
    {
        $this->addFieldToFilter(QueueDataInterface::DELIVERY_OPTION_ID_KEY, $deliveryOptionId);
        $this->addFieldToFilter(QueueDataInterface::ORDER_ADDRESS_ID_KEY, ['neq' => 'NULL']);
        $this->addFieldToSelect(QueueDataInterface::ORDER_ADDRESS_ID_KEY);
        $this->getSelect()->distinct(true);

        $data = $this->getConnection()->fetchCol($this->getSelect());

        return $data;
    }

    /**
     * Count how much rows in table with specified field.
     * Example:
     * SELECT delivery_day, SUM(1) AS total_votes FROM mageworx_dd_queue GROUP BY delivery_day ORDER BY total_votes DESC
     *
     * @param string $field
     * @return array
     */
    public function getItemsCountByField(string $field): array
    {
        $select = $this->getSelect();
        $select->reset('columns')
               ->columns([$field, 'total_count' => new \Zend_Db_Expr('SUM(1)')])
               ->group($field)
               ->order('total_count');

        $this->_renderFilters();

        $res = $this->_resource->getConnection()->fetchPairs($select);

        return $res;
    }
}
