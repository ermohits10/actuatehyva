<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Block\Adminhtml\Order\View;

/**
 * Class FinanceInfo
 * @package AutifyDigital\V12Finance\Block\Adminhtml\Order\View
 */
class FinanceInfo extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_order;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * FinanceInfo constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper,
        array $data = []
    ) {
        $this->_order = $registry->registry('current_order');
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        return $this->_order->getPayment();
    }

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getFinanceBlock()
    {
        $orderId = $this->getOrder()->getId();
        $financeApplication = $this->autifyDigitalHelper->getApplicationByOrderId($orderId);
        return $financeApplication;
    }

}
