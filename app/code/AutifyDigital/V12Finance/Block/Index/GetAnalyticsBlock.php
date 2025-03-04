<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Block\Index;

/**
 * Class GetAnalyticsBlock
 * @package AutifyDigital\V12Finance\Block\Index
 */
class GetAnalyticsBlock extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * GetAnalyticsBlock constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $_coreRegistry
     * @param \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $_coreRegistry,
        \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $_coreRegistry;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getFinanceAnalyticsData()
    {
        return $this->_coreRegistry->registry('analytics_finance_data');
    }

    public function getCoreMarketingConfig()
    {
        return $this->autifyDigitalHelper->getGoogleMarketingConfig();
    }

    public function getOrder($orderId)
    {
        return $this->autifyDigitalHelper->getOrder($orderId);
    }

}
