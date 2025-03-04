<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;

class QuoteLimitationMode implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            Helper::QUOTE_LIMITATION_MODE_OVERLOADING => [
                'label' => __('Yes'),
                'value' => Helper::QUOTE_LIMITATION_MODE_OVERLOADING
            ],
            Helper::QUOTE_LIMITATION_MODE_RESTRICTION => [
                'label' => __('No'),
                'value' => Helper::QUOTE_LIMITATION_MODE_RESTRICTION
            ]
        ];

        return $options;
    }
}
