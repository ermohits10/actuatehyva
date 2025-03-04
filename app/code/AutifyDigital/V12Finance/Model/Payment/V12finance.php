<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */


namespace AutifyDigital\V12Finance\Model\Payment;

use Magento\Payment\Model\InfoInterface;
use Magento\Framework\App\ObjectManager;
use \AutifyDigital\V12Finance\Helper\Data;

/**
 * Class V12finance
 * @package AutifyDigital\V12Finance\Model\Payment
 */
class V12finance extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * @var string
     */
    protected $_code = "v12finance";

    /**
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $this->apikey = $this->getConfigData('api_key');
        $this->retailer_id = $this->getConfigData('retailer_id');
        $this->retailer_guid = $this->getConfigData('retailer_guid');
        $this->_minOrderTotal = $this->getConfigData('min_order_total') - $quote->getShippingAddress()->getShippingAmount();
        $this->_maxOrderTotal = $this->getConfigData('max_order_total') + $quote->getShippingAddress()->getShippingAmount();

        $baseTotal = $quote->getBaseGrandTotal() - $quote->getShippingAddress()->getShippingAmount();

        if(
            !$quote &&
            (
                $baseTotal < $this->_minOrderTotal ||
                $baseTotal > $this->_maxOrderTotal ||
                $this->apikey == '' ||
                $this->retailer_id == '' ||
                $this->retailer_guid == ''
            )
        ) {
            return false;
        }

        $this->autifyDigitalHelper = ObjectManager::getInstance()->get(Data::class);
        $checkProductLevelFinanceConfig = $this->autifyDigitalHelper->getProductFinanceEnableConfig();

        $v12FinanceEnableProductArray =[];
        $quoteItems = $quote->getAllVisibleItems();
        $productDisableCategory = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/finance_category_option');
        $productDisableCategoryIds = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/finance_category_list');
        $productDisableCategoryIdsArray = [];
        if($productDisableCategory && $productDisableCategoryIds) {
            $productDisableCategoryIdsArray = explode(",", $productDisableCategoryIds);
        }
        $displayFinanceCategory= [];
        foreach ($quoteItems as $item) {
            $productId = $item->getProduct()->getId();
            $product = ObjectManager::getInstance()->get(\Magento\Catalog\Api\ProductRepositoryInterface::class)->getById($productId);
            $productLevelFinance = $product->getData('v12_finance_enable');
            if($productLevelFinance === '0') {
                $v12FinanceEnableProductArray[] = 0;
            }
            if($productDisableCategory) {
                foreach($product->getCategoryIds() as $productCategoryId) {
                    if(in_array($productCategoryId, $productDisableCategoryIdsArray)) {
                        $displayFinanceCategory[] = 1;
                    }
                }
            }
        }

        if($productDisableCategory) {
            if(in_array(1, $displayFinanceCategory)) {
                return false;
            }
        }

        if($checkProductLevelFinanceConfig === '1') {
            if(in_array(0, $v12FinanceEnableProductArray)) {
                return false;
            }
        }

        $financeEnable = $quote->getData('v12_finance_enable');

        if($financeEnable === "0") {
            return false;
        }
        return true;
    }

    public function refund(InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $this->autifyDigitalHelper = ObjectManager::getInstance()->get(Data::class);

        $getV12CoreConfig = $this->autifyDigitalHelper->getCoreConfig();
        $this->authenticationKey = $getV12CoreConfig['api_key'];
        $this->retailerId = $getV12CoreConfig['retailer_id'];
        $this->retailerGuid = $getV12CoreConfig['retailer_guid'];

        $orderId = $order->getId();
        $orderIncrementId = $order->getIncrementId();

        $getApplication = $this->autifyDigitalHelper->getApplicationByOrderId($orderId);
        $applicationId = $getApplication->getFinanceApplicationId();

        if($applicationId):

            $applicationCallArray = array(
                "ApplicationId" => $applicationId,
                "IncludeExtraDetails" => "false",
                "IncludeFinancials" => "false",
                "Retailer" => array(
                    "AuthenticationKey" => $this->authenticationKey,
                    "RetailerGuid" => $this->retailerGuid,
                    "RetailerId" => $this->retailerId
                )
            );

            $applicationCurlCall = $this->autifyDigitalHelper->callCurl(Data::V12_APPLICATION_STATUS_URL, $applicationCallArray);

            if (isset($applicationCurlCall['status']) && $applicationCurlCall['status'] != 'error') :
                $response = $applicationCurlCall['response'];
                $applicationStatusResponse = $response['Status'];
                if($applicationStatusResponse != '3') {
                    $callArray = array(
                        "ApplicationUpdateRequest" => array(
                            "ApplicationId" => $applicationId,
                            "SalesReference" => $orderIncrementId,
                            "SecondSalesReference" => 'autifydigital',
                            "Retailer" => array(
                                "AuthenticationKey" => $this->authenticationKey,
                                "RetailerGuid" => $this->retailerGuid,
                                "RetailerId" => $this->retailerId
                            )
                        )
                    );

                    $refundOrigAmount = $order->getOrigData('total_refunded');
                    $totalRefundAmount = $amount + $refundOrigAmount;

                    if($order->getGrandTotal() > $totalRefundAmount) {
                        $callArray['ApplicationUpdateRequest']['Update'] = 'PartialRefund';
                        $callArray['ApplicationUpdateRequest']['RefundAmount'] = $amount;
                    } else {
                        $callArray['ApplicationUpdateRequest']['Update'] = 'Cancel';
                    }

                    $this->autifyDigitalHelper->addLog($callArray, true);
                    $curlCall = $this->autifyDigitalHelper->callCurl(Data::V12_UPDATE_APPLICATION_URL, $callArray);
                    $this->autifyDigitalHelper->addLog($curlCall, true);
                    if (isset($curlCall['status']) && $curlCall['status'] !== 'error') :
                        $response = $curlCall['response'];
                        $status = $response['Status'];
                        $getApplication->setApplicationStatus($status)->save();
                        $orderStatus = str_replace(' ', '', strtolower(Data::V12_STATUS_ARRAY[$status]));
                        $comment = 'V12 Application Credit Status: ' . $orderStatus .' ('. $status . ') ';
                        if(!in_array($status, array(6, 7))) :
                            $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplication->getOrderId(), $comment, $this->autifyDigitalHelper->getStatusConfig($orderStatus), $applicationId, $status);
                        else:
                            $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplication->getOrderId(), $comment, 'finance_cancelled', $applicationId, $status);
                        endif;
                    endif;
                }
            endif;
        endif;
    }

}
