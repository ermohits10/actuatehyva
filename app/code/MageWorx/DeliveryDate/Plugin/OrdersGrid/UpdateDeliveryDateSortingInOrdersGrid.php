<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Plugin\OrdersGrid;

class UpdateDeliveryDateSortingInOrdersGrid
{
    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $result
     * @param string $field
     * @param string $direction
     * @return \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
     */
    public function afterSetOrder(
        \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject,
        \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $result,
                                                                 $field,
                                                                 $direction
    ) {
        if ($field === 'delivery_day') {
            $select            = $result->getSelect();
            $direction         = strtoupper($direction) == $subject::SORT_ORDER_ASC
                ? $subject::SORT_ORDER_ASC
                : $subject::SORT_ORDER_DESC;
            $deliveryTimeField = 'delivery_hours_from';

            $select->order(new \Zend_Db_Expr($field . ' ' . $direction));
            $select->order(new \Zend_Db_Expr($deliveryTimeField . ' ' . $direction));
        }

        return $result;
    }
}
