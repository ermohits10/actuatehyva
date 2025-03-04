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

class GetFinanceOption implements \Magento\Framework\Option\ArrayInterface
{
    protected $optionFactory;

    public function __construct(
        \AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions\CollectionFactory $optionFactory
    ) {
        $this->optionFactory = $optionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionFactory = $this->optionFactory->create();

        $optionArray = array();
        foreach ($optionFactory as $option) {
            $optionArray[] = ['value' => $option->getFinanceId() .'|'. $option->getFinanceGuid() .'|'. $option->getFinanceName() .'|'. $option->getInterestRate() .'|'. $option->getContractLength() .'|'. $option->getCalculationFactor().'|'. $option->getMinLoan().'|'. $option->getMaxLoan(), 'label' => __($option->getFinanceName())];
        }
        return $optionArray;
    }
}
