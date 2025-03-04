<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\DataProvider\DeliveryOption;

use Magento\Framework\Data\Collection;
use MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\Collection as RealCollection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class ShippingMethodsFilterStrategy implements AddFilterToCollectionInterface
{
    /**
     * @param Collection|RealCollection $collection
     * @param string $field
     * @param null $condition
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['eq'])) {
            $collection->addShippingMethodFilter($condition['eq']);
        }
    }
}
