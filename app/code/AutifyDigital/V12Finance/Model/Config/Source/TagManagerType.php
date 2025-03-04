<?php

namespace AutifyDigital\V12Finance\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class TagManagerType
 */
class TagManagerType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => 'Universal Analytics', 'value' => 'ga'];
        $options[] = ['label' => 'Google Analytics 4', 'value' => 'ga4'];
        return $options;
    }
}

