<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Month implements OptionSourceInterface
{
    const MONTH_ANY = 0;

    protected $months = [
        1  => 'January',
        2  => 'February',
        3  => 'March',
        4  => 'April',
        5  => 'May',
        6  => 'June',
        7  => 'July ',
        8  => 'August',
        9  => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    public function __construct()
    {
        //For translation generation:
        __('January');
        __('February');
        __('March');
        __('April');
        __('May');
        __('June');
        __('July ');
        __('August');
        __('September');
        __('October');
        __('November');
        __('December');
    }

    /**
     * Return array of options as value-label pairs
     *
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        $options = [];
        if (!$isMultiSelect) {
            $options[0] = [
                'label' => __('Each Month'),
                'value' => static::MONTH_ANY
            ];
        }

        foreach ($this->months as $index => $name) {
            $options[$index] = [
                'label' => __($name),
                'value' => $index
            ];
        }

        return $options;
    }
}
