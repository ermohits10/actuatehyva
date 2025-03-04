<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;

class GeneralQueue implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            Helper::GENERAL_QUEUE_GLOBAL              => [
                'label' => __('Global'),
                'value' => Helper::GENERAL_QUEUE_GLOBAL
            ],
            Helper::GENERAL_QUEUE_PER_DELIVERY_OPTION => [
                'label' => __('Per Delivery Option'),
                'value' => Helper::GENERAL_QUEUE_PER_DELIVERY_OPTION
            ]
        ];

        return $options;
    }
}
