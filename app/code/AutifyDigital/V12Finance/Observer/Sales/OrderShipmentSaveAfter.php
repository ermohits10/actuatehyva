<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Observer\Sales;

use \AutifyDigital\V12Finance\Helper\Data;

/**
 * Class OrderShipmentSaveAfter
 * @package AutifyDigital\V12Finance\Observer\Sales
 */
class OrderShipmentSaveAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    public function __construct(
        Data $autifyDigitalHelper
    ) {
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $getV12CoreConfig = $this->autifyDigitalHelper->getCoreConfig();
        $this->authenticationKey = $getV12CoreConfig['api_key'];
        $this->retailerId = $getV12CoreConfig['retailer_id'];
        $this->retailerGuid = $getV12CoreConfig['retailer_guid'];

        $shipment = $observer->getEvent()->getShipment();
        $orderId = $shipment->getOrderId();

        $getApplication = $this->autifyDigitalHelper->getApplicationByOrderId($orderId);
        $applicationId = $getApplication->getFinanceApplicationId();

        $requestPaymentConfig = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/request_payment_application');

        if($applicationId && (int)$getApplication->getApplicationStatus() === 5 && $requestPaymentConfig == 1):
            $callArray = array(
                "ApplicationUpdateRequest" => array(
                    "ApplicationId" => $applicationId,
                    "Update" => "RequestPayment",
                    "SalesReference" => $shipment->getOrder()->getIncrementId(),
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
            endif;
        endif;
    }
}

