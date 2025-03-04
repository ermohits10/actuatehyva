<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryOptionDataInterface;

class QuotesScope implements ArrayInterface
{
    /**
     * Return array of available quote limits
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Global (Unlimited)'),
                'value' => DeliveryOptionDataInterface::QUOTES_SCOPE_UNLIMITED
            ],
            [
                'label' => __('Per Day'),
                'value' => DeliveryOptionDataInterface::QUOTES_SCOPE_PER_DAY
            ],
            [
                'label' => __('Per Day of Week'),
                'value' => DeliveryOptionDataInterface::QUOTES_SCOPE_PER_DAY_OF_WEEK
            ]
        ];

        return $options;
    }
}
