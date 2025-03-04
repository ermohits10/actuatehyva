<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;

class ShippingMethodsChoiceLimiter implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('All Methods'),
                'value' => DeliveryOptionInterface::SHIPPING_METHODS_CHOICE_LIMIT_ALL_METHODS
            ],
            [
                'label' => __('Specific Methods'),
                'value' => DeliveryOptionInterface::SHIPPING_METHODS_CHOICE_LIMIT_SPECIFIC_METHODS
            ]
        ];
    }
}
