<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Controller\Adminhtml\Application;

use \AutifyDigital\V12Finance\Helper\Data;

class RequestPayment extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Data $autifyDigitalHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $applicationId = $this->getRequest()->getParam('application_id');
        $getApplication = $this->autifyDigitalHelper->getApplicationById($applicationId);

        $requestPaymentConfig = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/request_payment_application');

        if($applicationId && (int)$getApplication->getApplicationStatus() === 5 && $requestPaymentConfig == 1):
            $getV12CoreConfig = $this->autifyDigitalHelper->getCoreConfig();
            $this->authenticationKey = $getV12CoreConfig['api_key'];
            $this->retailerId = $getV12CoreConfig['retailer_id'];
            $this->retailerGuid = $getV12CoreConfig['retailer_guid'];

            $callArray = array(
                "ApplicationUpdateRequest" => array(
                    "ApplicationId" => $applicationId,
                    "Update" => "RequestPayment",
                    "SalesReference" => $getApplication->getOrderIncrementId(),
                    "Retailer" => array(
                        "AuthenticationKey" => $this->authenticationKey,
                        "RetailerGuid" => $this->retailerGuid,
                        "RetailerId" => $this->retailerId
                    )
                )
            );
            $this->autifyDigitalHelper->addLog($callArray, true);
            $curlCall = $this->autifyDigitalHelper->callCurl(Data::V12_UPDATE_APPLICATION_URL, $callArray);
            $this->autifyDigitalHelper->addLog($curlCall, true);
            if (isset($curlCall['status']) && $curlCall['status'] !== 'error') :
                $response = $curlCall['response'];
                $status = $response['Status'];
                $getApplication->setApplicationStatus($status)->save();
                $orderStatus = str_replace(' ', '', strtolower(Data::V12_STATUS_ARRAY[$status]));
                $comment = 'V12 Application Credit Status: ' . $orderStatus .' ('. $status . ') ';
                $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplication->getOrderId(), $comment, $this->autifyDigitalHelper->getStatusConfig($orderStatus), $applicationId, $status);
                $this->messageManager->addSuccessMessage(__('We have requested payment.'));
            else:
                $this->messageManager->addErrorMessage(__('Something went wrong. Please check the logs'));
            endif;
        else:
            $this->messageManager->addErrorMessage(__('Something went wrong. Please check the logs'));
        endif;
        return $resultRedirect->setPath('*/*/');
    }
}

