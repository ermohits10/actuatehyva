<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Product\Attribute\Source;

class WeekDays extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \MageWorx\DeliveryDate\Model\Source\WorkingDays
     */
    private $daysSource;

    /**
     * WeekDays constructor.
     *
     * @param \MageWorx\DeliveryDate\Model\Source\WorkingDays $daysSource
     */
    public function __construct(
        \MageWorx\DeliveryDate\Model\Source\WorkingDays $daysSource
    ) {
        $this->daysSource = $daysSource;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->daysSource->toOptionArray();
    }
}
