<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Block\Adminhtml\Order;

use Magento\Framework\DataObject;

/**
 * Class FinanceTotal
 * @package AutifyDigital\V12Finance\Block\Adminhtml\Order
 */
class FinanceTotal extends \Magento\Sales\Block\Adminhtml\Totals
{

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * FinanceTotal constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     * @param \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    /**
     * @return $this
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

                $financeAmount = new DataObject(
                    [
                        'code' => 'paid',
                        'strong' => false,
                        'value' =>  $applicationData->getDepositAmount(),
                        'label' => __('Total Paid'),
                        'area' => 'footer',
                    ]
                );
                $parent->addTotal($financeAmount, 'paid');

                if($parent->getSource()->getTotalRefunded() > 0) {
                    $totalRefunded = new DataObject(
                        [
                            'code' => 'refunded',
                            'strong' => false,
                            'value' =>  $applicationData->getDepositAmount(),
                            'label' => __('Total Refunded'),
                            'area' => 'footer',
                        ]
                    );
                    $parent->addTotal($totalRefunded, 'refunded');
                }

                $grandTotal = new DataObject(
                    [
                        'code' => 'grand_total',
                        'field' => 'grand_total',
                        'strong' => true,
                        'value' => $applicationData->getDepositAmount(),
                        'label' => __('Grand Total'),
                        'area' => 'footer',
                    ]
                );
                $parent->addTotal($grandTotal, 'grand_total');

                $totalRepayable = new DataObject(
                    [
                        'code' => 'total_amount_repayable',
                        'field' => 'total_amount_repayable',
                        'strong' => true,
                        'value' => $applicationData->getTotalAmountPayable(),
                        'label' => __('Total Amount Repayable'),
                        'area' => 'footer',
                    ]
                );
                $parent->addTotal($totalRepayable, 'last');

            }
            return $this;
        }
    }

}
