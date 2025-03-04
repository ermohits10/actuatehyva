<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class GetApplicationStatus
 */
class GetApplicationStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => 'Please Select', 'value' => ''];
        $options[] = ['label' => 'Error', 'value' => '0'];
        $options[] = ['label' => 'Acknowledged', 'value' => '1'];
        $options[] = ['label' => 'Referred', 'value' => '2'];
        $options[] = ['label' => 'Declined', 'value' => '3'];
        $options[] = ['label' => 'Accepted', 'value' => '4'];
        $options[] = ['label' => 'AwaitingFulfilment', 'value' => '5'];
        $options[] = ['label' => 'Payment Requested', 'value' => '6'];
        $options[] = ['label' => 'Payment Processed', 'value' => '7'];
        $options[] = ['label' => 'Pending Application', 'value' => '9'];
        $options[] = ['label' => 'Cancelled', 'value' => '100'];
        return $options;
    }
}

