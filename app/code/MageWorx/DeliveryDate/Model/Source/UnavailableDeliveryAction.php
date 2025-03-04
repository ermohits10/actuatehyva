<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class UnavailableDeliveryAction
 *
 * Contains options for the action when delivery date is unavailable for a product.
 */
class UnavailableDeliveryAction implements ArrayInterface
{
    const DISPLAY_NOTHING = '0';
    const DISPLAY_ERROR_MESSAGE = '1';

    /**
     * Return array of available actions.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Display Nothing'),
                'value' => self::DISPLAY_NOTHING
            ],
            [
                'label' => __('Display Error Message'),
                'value' => self::DISPLAY_ERROR_MESSAGE
            ]
        ];
    }
}
