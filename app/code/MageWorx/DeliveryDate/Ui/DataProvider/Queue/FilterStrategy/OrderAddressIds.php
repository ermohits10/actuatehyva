<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\DataProvider\Queue\FilterStrategy;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection as RealCollection;

class OrderAddressIds implements AddFilterToCollectionInterface
{
    /**
     * @param Collection|RealCollection $collection
     * @param string $field
     * @param null|string|array $condition
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $field = 'main_table.entity_id';
        $collection->addFieldToFilter($field, $condition);
    }
}
