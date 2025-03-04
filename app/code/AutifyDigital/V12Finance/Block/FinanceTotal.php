<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Block;

use Magento\Framework\DataObject;

/**
 * Class FinanceTotal
 * @package AutifyDigital\V12Finance\Block
 */
class FinanceTotal extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * FinanceTotal constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    /**
     * Initialization of totals for admin view of order
     * @return void
     */
    public function initTotals()
    {

        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
        $orderId = $order->getId();
        $payment = $order->getPayment()->getMethodInstance()->getCode();
        if($payment === 'v12finance') {

            $applicationData = $this->autifyDigitalHelper->getApplicationByOrderId($orderId);

            if(!empty($applicationData->getData())) {

                $v12FinaneAmount = new DataObject(
                    [
                        'code' => 'v12_finance_amount',
                        'strong' => false,
                        'value' => - $applicationData->getFinanceAmount(),
                        'label' => __('Financed Amount'),
                    ]
                );
                $parent->addTotal($v12FinaneAmount, 'v12_finance_amount');

                $grandTotal = new DataObject(
                    [
                        'code' => 'grand_total',
                        'field' => 'grand_total',
                        'strong' => true,
                        'value' => $applicationData->getDepositAmount(),
                        'label' => __('Grand Total'),
                    ]
                );
                $parent->addTotal($grandTotal, 'grand_total');

                $totalRepayable = new DataObject(
                    [
                        'code' => 'total_repayable',
                        'field' => 'total_repayable',
                        'strong' => true,
                        'value' => $applicationData->getTotalAmountPayable(),
                        'label' => __('Total Amount Repayable'),
                    ]
                );
                $parent->addTotal($totalRepayable, 'last');
            }

        }

    }

}
