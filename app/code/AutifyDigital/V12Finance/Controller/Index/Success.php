<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Controller\Index;

use \AutifyDigital\V12Finance\Helper\Data;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Success
 * @package AutifyDigital\V12Finance\Controller\Index
 */
class Success extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * ApplicationSuccess constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $_coreRegistry
     * @param Data $autifyDigitalHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $_coreRegistry,
        Data $autifyDigitalHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $_coreRegistry;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pageTitle = "V12 Order";
        if($this->getRequest()->getParams()) :
            $applicationId = $this->getRequest()->getParam('id');
            $getApplicationById = $this->autifyDigitalHelper->getApplicationById($applicationId);
            if($getApplicationById->getOrderId()):
                $order = $this->autifyDigitalHelper->getOrder($getApplicationById->getOrderId());
                if($order) :
                    $applicationStatus = (int)$getApplicationById->getApplicationStatus();
                    if($applicationStatus === 2) {
                        $pageTitle = "Almost There";
                        $pageText = $this->autifyDigitalHelper->getConfig("v12finance/checkout_success_configuration/referred_status_message");
                    } else if($applicationStatus === 5) {
                        $pageTitle = "Order Success";
                        $pageText = $this->autifyDigitalHelper->getConfig("v12finance/checkout_success_configuration/signed_status_message");
                    } else {
                        $pageTitle = "V12 Application";
                        $pageText = $this->autifyDigitalHelper->getConfig("v12finance/checkout_success_configuration/other_status_message");
                    }
                    $financeData = array(
                        'order_id' => $order->getId(),
                        'order_increment_id' => $order->getIncrementId(),
                        'application_id' => $applicationId,
                        'application_status' => $applicationStatus,
                        'page_text' => $pageText,
                        'ads_sent' => $getApplicationById->getAdsSent(),
                        'layer_sent' => $getApplicationById->getLayerSent(),
                        'analytics_sent' => $getApplicationById->getAnalyticsSent()
                    );
                    $this->_coreRegistry->register('v12finance_success_page', $financeData);
                else :
                    $this->_coreRegistry->register('v12finance_success_page', '');
                endif;
            else :
                $this->_coreRegistry->register('v12finance_success_page', '');
            endif;
        else :
            $this->_coreRegistry->register('v12finance_success_page', '');
        endif;
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($pageTitle);
        return $resultPage;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}

