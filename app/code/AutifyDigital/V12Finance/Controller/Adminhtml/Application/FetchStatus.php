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
use \Magento\Framework\App\ObjectManager;

class FetchStatus extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    protected $redirect;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Data $autifyDigitalHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Data $autifyDigitalHelper,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        $this->redirect = $redirect;
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

        if($applicationId && $getApplication->getApplicationStatus()) :

            $getV12CoreConfig = $this->autifyDigitalHelper->getCoreConfig();
            $this->authenticationKey = $getV12CoreConfig['api_key'];
            $this->retailerId = $getV12CoreConfig['retailer_id'];
            $this->retailerGuid = $getV12CoreConfig['retailer_guid'];
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
            $applicationStatus = (int)$getApplication->getApplicationStatus();

            if (isset($curlCall['status']) && $curlCall['status'] != 'error') :
                $response = $curlCall['response'];
                $applicationStatusResponse = $response['Status'];

                if(isset($applicationStatusResponse) && $applicationStatus !== $applicationStatusResponse) :
                    $order = $this->autifyDigitalHelper->getOrder($getApplication->getOrderId());
                    if($order->getState() == 'canceled' || $order->getState() == 'closed') {
                        if(in_array($applicationStatusResponse, array(2,4,5,6,7,200))) {
                            $commentArray = "";
                            $commentArray .= "---Start Autify Digital V12 Finance History---&lt;br/&gt;";
                            $commentArray .= "Order was declined earlier. Please process it manually.";
                            $commentArray .= "&lt;br/&gt;---End Autify Digital V12 Finance History---";
                            $order->addStatusHistoryComment($commentArray, false);
                            $order->save();

                            $this->messageManager->addErrorMessage(__('Application %1 status is same as the Magento.', $applicationId));
                            return $resultRedirect->setPath($this->redirect->getRedirectUrl());
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

                            $this->messageManager->addErrorMessage(__('Some of the products are out of stock. Please check this SKUs: ', implode(", ", $outOfSkus)));
                            return $resultRedirect->setPath($this->redirect->getRedirectUrl());
                        }
                    }

                    if($applicationStatusResponse === 5) {
                        $this->autifyDigitalHelper->callV12ClickAndCollect($getApplication, $applicationId);
                    }

                    $orderStatus = str_replace(' ', '', strtolower(Data::V12_STATUS_ARRAY[$applicationStatusResponse]));
                    $comment = 'V12 Application Credit Status: ' . $orderStatus .' ('. $applicationStatusResponse . ') ';
                    if(!in_array($applicationStatusResponse, array(6, 7))) :
                        $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplication->getOrderId(), $comment, $this->autifyDigitalHelper->getStatusConfig($orderStatus), $applicationId, $applicationStatusResponse);
                    else:
                        $this->autifyDigitalHelper->updateApplicationOrOrderStatus($getApplication->getOrderId(), 'Application '  . $applicationId . ' sent to V12', 'processing', $applicationId, $applicationStatusResponse);
                    endif;
                    $this->autifyDigitalHelper->addLog($response, true);
                    $getApplication->setApplicationStatus($applicationStatusResponse)->save();
                    //Need to create function
                    $this->autifyDigitalHelper->updateAddress($getApplication->getOrderId(), $response, $getApplication);
                    $this->messageManager->addSuccessMessage(__('Application %1 is fetched.', $applicationId));
                else:
                    $this->messageManager->addErrorMessage(__('Application %1 status is same as the Magento.', $applicationId));
                    $this->autifyDigitalHelper->addLog(__('Application %1 status is same as the Magento.', $applicationId));
                endif;
            else:
                $this->messageManager->addErrorMessage(__('Application %1 has an update but CURL call has failed.', $applicationId));
                $this->autifyDigitalHelper->addLog(__('Application %1 has an update but CURL call has failed.', $applicationId));
            endif;
        else :
            $this->messageManager->addErrorMessage(__('The application is not in the database.'));
        endif;

        return $resultRedirect->setPath($this->redirect->getRedirectUrl());
    }
}

