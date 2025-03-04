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
 * Class Response
 * @package AutifyDigital\V12Finance\Controller\Index
 */
class Response extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param Data $autifyDigitalHelper
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Data $autifyDigitalHelper
    ) {
        parent::__construct($context);
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        $this->_baseUrl = $this->autifyDigitalHelper->getBaseUrl();
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
        $getV12CoreConfig = $this->autifyDigitalHelper->getCoreConfig();
        $this->authenticationKey = $getV12CoreConfig['api_key'];
        $this->retailerId = $getV12CoreConfig['retailer_id'];
        $this->retailerGuid = $getV12CoreConfig['retailer_guid'];
        $this->autifyDigitalHelper->addLog("Response Application Call");
        $this->autifyDigitalHelper->addLog($this->getRequest()->getParams(), true);

        if(
            $this->getRequest()->getParams() &&
            $this->getRequest()->getParam('SR') &&
            $this->getRequest()->getParam('REF')
        ) :
            $orderId = $this->getRequest()->getParam('SR');
            $applicationId = $this->getRequest()->getParam('REF');
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

                    $responseSuccess = true;

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
                                if($applicationStatusResponse === 2) {
                                    $this->messageManager->addSuccessMessage('Thanks for your order.');
                                    $this->_redirect($this->_baseUrl.'v12finance/index/referred/id/'.$applicationId);
                                } else {
                                    $this->messageManager->addSuccessMessage('Thanks for your order.');
                                    $this->_redirect($this->_baseUrl.'v12finance/index/success/id/'.$applicationId);
                                }
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
                                $this->messageManager->addSuccessMessage('Thanks for your order.');
                                $this->_redirect($this->_baseUrl.'v12finance/index/success/id/'.$applicationId);
                            }
                        }

                        if($applicationStatusResponse === 5) {
                            $this->autifyDigitalHelper->callV12ClickAndCollect($getApplicationById, $applicationId);
                        }

                        $orderStatus = str_replace(' ', '', strtolower(Data::V12_STATUS_ARRAY[$applicationStatusResponse]));
                        $comment = 'V12 Application Credit Status: ' . $orderStatus .' ('. $applicationStatusResponse . ') ';
                        if(!in_array($applicationStatusResponse, array(6, 7))) :
                            $responseUpdate = $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplicationById->getOrderId(), $comment, $this->autifyDigitalHelper->getStatusConfig($orderStatus), $applicationId, $applicationStatusResponse, true);
                        else:
                            $responseUpdate = $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplicationById->getOrderId(), 'Application '  . $applicationId . ' sent to V12', 'processing', $applicationId, $applicationStatusResponse, true);
                        endif;
                        $this->autifyDigitalHelper->addLog($response, true);
                        $getApplicationById->setApplicationStatus($applicationStatusResponse);
                        if(null !== $response['AuthorisationCode']) {
                            $getApplicationById->setAuthorizationCode($response['AuthorisationCode']);
                        }
                        $getApplicationById->save();
                        //Need to create function
                        $this->autifyDigitalHelper->updateAddress($getApplicationById->getOrderId(), $response, $getApplicationById);
                        $this->autifyDigitalHelper->addLog('Response Updated');
                        if($responseUpdate === true) :
                            $responseSuccess = true;
                        else :
                            $responseSuccess = false;
                        endif;
                    endif;

                    if(in_array($applicationStatusResponse, array(0, 100, 3))) :
                        $responseSuccess = false;
                    endif;

                    if($responseSuccess === true):
                        if($applicationStatusResponse === 2) {
                            $this->messageManager->addSuccessMessage('Your order has been referred.');
                            $this->_redirect($this->_baseUrl.'v12finance/index/referred/id/'.$applicationId);
                        } else {
                            $this->messageManager->addSuccessMessage('Thanks for your order. We have processed your order.');
                            $this->_redirect($this->_baseUrl.'v12finance/index/success/id/'.$applicationId);
                        }
                    else:
                        $this->autifyDigitalHelper->restoreQuote();
                        $this->messageManager->addErrorMessage('There is an error in your application. You can proceed your order with other payment.');
                        if(
                            $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_enable') == 1 &&
                            !empty($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_url'))
                        ) {
                            $this->_redirect($this->_baseUrl.$this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_url'));
                        } else {
                            $this->_redirect($this->_baseUrl.'checkout/cart');
                        }
                    endif;
                else:
                    $this->_redirect($this->_baseUrl);
                endif;
            else:
                $this->_redirect($this->_baseUrl);
            endif;
        else:
            $this->_redirect($this->_baseUrl);
        endif;

    }
}

