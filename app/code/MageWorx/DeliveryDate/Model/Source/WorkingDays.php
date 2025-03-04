<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class WorkingDays implements ArrayInterface
{
    /**
     * Return array of working days.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $workingDays = [
            [
                'label' => __('Sunday'),
                'value' => 'sunday'
            ],
            [
                'label' => __('Monday'),
                'value' => 'monday'
            ],
            [
                'label' => __('Tuesday'),
                'value' => 'tuesday'
            ],
            [
                'label' => __('Wednesday'),
                'value' => 'wednesday'
            ],
            [
                'label' => __('Thursday'),
                'value' => 'thursday'
            ],
            [
                'label' => __('Friday'),
                'value' => 'friday'
            ],
            [
                'label' => __('Saturday'),
                'value' => 'saturday'
            ],
        ];

        return $workingDays;
    }

    /**
     * Return Data Object data in array format.
     *
     * @return array
     */
    public function __toArray()
    {
        return [
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        ];
    }

    /**
     * @return array|string[]
     */
    public static function getDaysArray(): array
    {
        return [
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        ];
    }
}
