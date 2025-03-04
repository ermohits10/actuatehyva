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

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use \AutifyDigital\V12Finance\Helper\Data;
use Magento\Framework\App\ObjectManager;

/**
 * Class ApplicationStatusUpdate
 * @package AutifyDigital\V12Finance\Controller\Index
 */
class ApplicationStatusUpdate extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param Data $autifyDigitalHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        Data $autifyDigitalHelper
    ) {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->jsonResultFactory->create();
        $getV12CoreConfig = $this->autifyDigitalHelper->getCoreConfig();
        $this->authenticationKey = $getV12CoreConfig['api_key'];
        $this->retailerId = $getV12CoreConfig['retailer_id'];
        $this->retailerGuid = $getV12CoreConfig['retailer_guid'];
        $this->autifyDigitalHelper->addLog("Update Application Call");
        $this->autifyDigitalHelper->addLog($this->getRequest()->getParams(), true);
        if(
            $this->getRequest()->getParams() &&
            $this->getRequest()->getParam('ApplicationId') &&
            $this->getRequest()->getParam('SalesReference') &&
            $this->getRequest()->getParam('Status')
        ) :
            $orderId = $this->getRequest()->getParam('SalesReference');
            $applicationId = $this->getRequest()->getParam('ApplicationId');
            $getApplicationById = $this->autifyDigitalHelper->getApplicationById($applicationId);

            if($getApplicationById->getOrderIncrementId() === $orderId) :

                $applicationStatus = (int)$getApplicationById->getApplicationStatus();

                $callArray = array(
                    "ApplicationId" => $applicationId,
                    "IncludeExtraDetails" => "true",
                    "IncludeFinancials" => "false",
                    "Retailer" => array(
                        "AuthenticationKey" => $this->authenticationKey,
                        "RetailerGuid" => $this->retailerGuid,
                        "RetailerId" => $this->retailerId
                    )
                );

                $this->autifyDigitalHelper->addLog($callArray, true);
                $curlCall = $this->autifyDigitalHelper->callCurl(Data::V12_APPLICATION_STATUS_URL, $callArray);
                $this->autifyDigitalHelper->addLog($curlCall, true);

                if (isset($curlCall['status']) && $curlCall['status'] != 'error') :
                    $response = $curlCall['response'];
                    $applicationStatusResponse = $response['Status'];

                    if(isset($applicationStatusResponse) && $applicationStatus !== $applicationStatusResponse) :
                        $order = $this->autifyDigitalHelper->getOrder($getApplicationById->getOrderId());
                        if($order->getState() == 'canceled' || $order->getState() == 'closed') {
                            if(in_array($applicationStatusResponse, array(2,4,5,6,7,200))) {
                                $commentArray = "";
                                $commentArray .= "---Start Autify Digital V12 Finance History---&lt;br/&gt;";
                                $commentArray .= "Order was declined earlier. Please process it manually.";
                                $commentArray .= "&lt;br/&gt;---End Autify Digital V12 Finance History---";
                                $order->addStatusHistoryComment($commentArray, false);
                                $order->save();

                                $this->autifyDigitalHelper->addLog('HTTP Code: 200');
                                $result->setHttpResponseCode('200');
                                $result->setData(['response' => __('Success.')]);
                                return $result;
                            }
                        }

                        if(
                            $applicationStatusResponse === 5 &&
                            $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/outofstock_check') == '1'
                        ) {
                            $outOfSkus = $processOrder =[];
                            foreach ($order->getItems() as $item) {
                                if($item->getProductType() === 'simple') {
                                    $itemSku = $item->getSku();
                                    $qtyOrdered = intval($item->getQtyOrdered());
                                    $websiteId = (int)$order->getStore()->getWebsiteId();

                                    $stockInterface = $this->autifyDigitalHelper->getStockItem($item->getProduct());
                                    if($this->autifyDigitalHelper->checkMSIEnable()) {
                                        //Check the module is enabled
                                        //MSI
                                        $itemSkuSimple = $item->getProduct()->getSku();
                                        $stockByWebsiteIdResolver = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface::class);
                                        $stockId = (int)$stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
                                        $getProductSalableQtyInterface = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface::class);
                                        $salableQty = $getProductSalableQtyInterface->execute($itemSkuSimple, $stockId);

                                    } else {
                                        //Check the module isnt enabled
                                        $salableQty = $stockInterface->getQty();
                                    }

                                    $backOrder = $stockInterface->getBackorders();
                                    if($backOrder != 0) {
                                        continue;
                                    }
                                    $salableQty = intval($salableQty);
                                    if($salableQty < $qtyOrdered) {
                                        $processOrder[] = 0;
                                        $outOfSkus[] = $itemSku;
                                    }
                                }
                            }
                            if(in_array(0, $processOrder)) {
                                $commentArray = "";
                                $commentArray .= "---Start Autify Digital V12 Finance History---&lt;br/&gt;";
                                $commentArray .= "Some of the products are out of stock. Please check this SKUs: " . implode(", ", $outOfSkus);
                                $commentArray .= "&lt;br/&gt;---End Autify Digital V12 Finance History---";
                                $order->addStatusHistoryComment($commentArray, false);
                                $order->save();

                                $this->autifyDigitalHelper->addLog('HTTP Code: 200');
                                $result->setHttpResponseCode('200');
                                $result->setData(['response' => __('Success.')]);
                                return $result;
                            }
                        }

                        if($applicationStatusResponse === 5) {
                            $this->autifyDigitalHelper->callV12ClickAndCollect($getApplicationById, $applicationId);
                        }

                        $orderStatus = str_replace(' ', '', strtolower(Data::V12_STATUS_ARRAY[$applicationStatusResponse]));
                        $comment = 'V12 Application Credit Status: ' . $orderStatus .' ('. $applicationStatusResponse . ') ';
                        if(!in_array($applicationStatusResponse, array(6, 7))) :
                            $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplicationById->getOrderId(), $comment, $this->autifyDigitalHelper->getStatusConfig($orderStatus), $applicationId, $applicationStatusResponse);
                        else:
                            $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplicationById->getOrderId(), 'Application '  . $applicationId . ' sent to V12', 'processing', $applicationId, $applicationStatusResponse);
                        endif;
                        $this->autifyDigitalHelper->addLog($response, true);
                        $getApplicationById->setApplicationStatus($applicationStatusResponse)->save();
                        //Need to create function
                        $this->autifyDigitalHelper->updateAddress($getApplicationById->getOrderId(), $response, $getApplicationById);
                        $this->autifyDigitalHelper->addLog('HTTP Code: 200');
                        $result->setHttpResponseCode('200');
                        $result->setData(['response' => __('Success.')]);
                    else:
                        $this->autifyDigitalHelper->addLog(__('Application %1 status is same as the Magento.', $applicationId));
                        $result->setHttpResponseCode('200');
                        $result->setData(['response' => __('Success.')]);
                    endif;
                else:
                    $this->autifyDigitalHelper->addLog(__('Application %1 has an update but CURL call has failed.', $applicationId));
                    $result->setHttpResponseCode('200');
                    $result->setData(['response' => __('Success.')]);
                endif;
            else:
                $this->autifyDigitalHelper->addLog(__('Application %1 has an update but Order ID is wrong from the webhook.', $applicationId));
                $result->setHttpResponseCode('200');
                $result->setData(['response' => __('Success.')]);
            endif;
        else:
            $this->autifyDigitalHelper->addLog('HTTP Code: 403');
            $this->autifyDigitalHelper->addLog($this->getRequest()->getParams(), true);
            $this->autifyDigitalHelper->addLog('Required Parameters Not Provided');
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $result->setData(['error_message' => __('Error While fetching the data.')]);
        endif;
        return $result;
    }

}

