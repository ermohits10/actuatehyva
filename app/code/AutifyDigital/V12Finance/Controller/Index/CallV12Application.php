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
use Magento\Sales\Model\Order;

/**
 * Class CallV12Application
 * @package AutifyDigital\V12Finance\Controller\Index
 */
class CallV12Application extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Mail
     */
    protected $mailHelper;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @var $_baseUrl
     */
    protected $_baseUrl;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Data $autifyDigitalHelper,
        \AutifyDigital\V12Finance\Helper\Mail $mailHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        $this->mailHelper = $mailHelper;
        $this->_baseUrl = $this->autifyDigitalHelper->getBaseUrl();
        parent::__construct($context);
        $this->applicationFactory = $this->autifyDigitalHelper->getApplicationFactory();
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $currentOrder = $this->autifyDigitalHelper->getCurrentOrder();
            $customerQuote = $this->autifyDigitalHelper->getQuote();
            if($this->getRequest()->getParams() && $customerQuote) {

                $this->autifyDigitalHelper->addLog('V12 Finance Application Calling');
                $id = $this->getRequest()->getParam('id');
                $deposit = $this->getRequest()->getParam('deposit');
                $totalAmountPayable = $this->getRequest()->getParam('totalAmountPayable');
                $orderAmount = $currentOrder->getGrandTotal();
                $getV12CoreConfig = $this->autifyDigitalHelper->getCoreConfig();

                $v12FinanceGUID = $v12FinanceId = $contractLength = '';
                $financeTitle = $financeTax = "";

                if($id){
                    $productFinanceArray = explode('|', $id);
                    $v12FinanceId = $productFinanceArray[0];
                    $v12FinanceGUID = $productFinanceArray[1];
                    $contractLength = $productFinanceArray[4];
                    $financeTitle = $productFinanceArray[2];
                    $financeTax = $productFinanceArray[3];
                }

                $orderItems = array();
                $itemsArray = array();
                $i = 0;
                foreach ($currentOrder->getAllVisibleItems() as $item) {
                    $orderItems[$i]['Qty'] = $item->getQtyOrdered();
                    $orderItems[$i]['SKU'] = $item->getSku();
                    $orderItems[$i]['Item'] = $item->getName();
                    $orderItems[$i]['Price'] = $item->getPrice();

                    if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/analytics_enable')) { 
                        if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/google_analytics_type') == 'ga4') { 
                            $itemsArray[$i]['item_id'] = $item->getSku();
                            $itemsArray[$i]['item_name'] = $item->getName();
                            $itemsArray[$i]['quantity'] = number_format($item->getQtyOrdered());
                            $itemsArray[$i]['price'] = $item->getPrice();
                            $itemsArray[$i]['discount'] = number_format($item->getDiscountAmount(), 2, ".", "");
                            $itemsArray[$i]['index'] = $i;
                        }
                        if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/google_analytics_type') == 'ga') { 
                            $itemsArray[$i]['id'] = $item->getSku();
                            $itemsArray[$i]['name'] = $item->getName();
                            $itemsArray[$i]['quantity'] = number_format($item->getQtyOrdered());
                            $itemsArray[$i]['price'] = $item->getPrice();
                            $itemsArray[$i]['discount'] = number_format($item->getDiscountAmount(), 2, ".", "");
                        }
                    }

                    $i++;
                }

                $shippingMethod = $currentOrder->getShippingMethod();

                $orderItems[$i]['Qty'] = 1;
                $orderItems[$i]['SKU'] = $shippingMethod;
                $orderItems[$i]['Item'] = $shippingMethod;
                $orderItems[$i]['Price'] = $currentOrder->getShippingAmount();

                $orderAnalyticsAndInStoreArray = array();
                if($getV12CoreConfig['click_and_collect'] === '1' &&
                    $shippingMethod === $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/click_and_collect_shipment_code_backend')) {
                    //DeliveryAddresss
                    $getShippingAddress = $currentOrder->getShippingAddress();
                    $streetArray = $getShippingAddress->getStreet();
                    $street = implode(', ', $streetArray);

                    $orderAnalyticsAndInStoreArray['DeliveryAddress'] = array(
                        "BuildingName" => $getShippingAddress->getName(),
                        "Street" => $street,
                        "TownOrCity" => $getShippingAddress->getCity(),
                        "Postcode" => $getShippingAddress->getPostcode(),
                        "Locality" => $getShippingAddress->getRegion(),
                        "Country" => $getShippingAddress->getCountryId()
                    );
                    $orderAnalyticsAndInStoreArray['DeliveryOption'] = "StoreCollect";
                }

                $coreOrderRequestArray = array(
                    "Lines" => $orderItems,
                    "CashPrice" => $orderAmount,//$orderAmount
                    "Deposit" => "$deposit",//$depositAmount
                    "DuplicateSalesReferenceMethod" => "ShowError",
                    "ProductGuid" => $v12FinanceGUID,
                    "ProductId" => $v12FinanceId,
                    "SalesReference" => $currentOrder->getIncrementId(),
                    "SecondSalesReference" => "autifydigital"
                );

                if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/analytics_enable')) {
                    $storeName = $this->autifyDigitalHelper->getConfig('general/store_information/name');

                    $analyticsRequest = array(
                        "transaction_id" => $currentOrder->getIncrementId(),
                        "affiliation" => htmlspecialchars($storeName, ENT_QUOTES),
                        "value" => $orderAmount,
                        "tax" => number_format($currentOrder->getTaxAmount(), 2, ".", ""),
                        "shipping" => number_format($currentOrder->getBaseShippingAmount(), 2, ".", ""),
                        "currency" => "GBP",
                        "items" => $itemsArray
                    );

                    if($currentOrder->getCouponCode()) { 
                        $analyticsRequest["coupon"] = $currentOrder->getCouponCode();
                    }

                    $orderAnalyticsAndInStoreArray["PurchaseEventPayload"] = $this->autifyDigitalHelper->getJsonHelper()->jsonEncode($analyticsRequest);
                } else {
                    $this->autifyDigitalHelper->addLog("Analytics was disabled for V12 page" . $currentOrder->getIncrementId() , true);
                }

                $orderRequestArray = array_merge($coreOrderRequestArray, $orderAnalyticsAndInStoreArray);

                $v12RequestArray = array(
                    "Customer" => array(
                        "EmailAddress" => $currentOrder->getCustomerEmail(),
                        "FirstName" => $currentOrder->getCustomerFirstname(),
                        "LastName" => $currentOrder->getCustomerLastname(),
                    ),
                    "Order" => $orderRequestArray,
                    "Retailer" => array(
                        "AuthenticationKey" => $getV12CoreConfig['api_key'],
                        "RetailerGuid" => $getV12CoreConfig['retailer_guid'],
                        "RetailerId" => $getV12CoreConfig['retailer_id']
                    )
                );

                $this->saveFinance($currentOrder, $deposit, $totalAmountPayable, $v12FinanceId, $v12FinanceGUID, $contractLength);
                $this->autifyDigitalHelper->addLog($v12RequestArray, true);
                $v12FinanceCurlCall = $this->autifyDigitalHelper->callCurl(Data::V12_APPLICATION_URL, $v12RequestArray);

                if ($v12FinanceCurlCall['status'] === 'error') {
                    $this->applicationFactory->setApplicationStatus(0)->save();
                    $this->autifyDigitalHelper->addLog('Application was cancelled');

                    $this->autifyDigitalHelper->updateApplicationOrOrderStatus($currentOrder->getId(), 'Error in Application', Order::STATE_CANCELED, '', 0);

                    $this->messageManager->addErrorMessage(__('There is an error with the finance.'));
                    if(
                        $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_enable') == 1 &&
                        !empty($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_url'))
                    ) {
                        $this->_redirect($this->_baseUrl.$this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_url'));
                    } else {
                        $this->_redirect($this->_baseUrl.'checkout/cart');
                    }
                    //Email
                } else {
                    $jsonDecodeArray = $v12FinanceCurlCall['response'];

                    $checkErrors = '';
                    if(isset($jsonDecodeArray['Errors'][0]) && !isset($jsonDecodeArray['ApplicationFormUrl'])){
                        $this->applicationFactory->setApplicationStatus(0)->save();
                        $checkErrors = '';
                        if(isset($jsonDecodeArray['Errors'][0]['Description'])) {
                            $checkErrors = $jsonDecodeArray['Errors'][0]['Description'];
                            $this->autifyDigitalHelper->addLog($checkErrors);
                            $this->messageManager->addErrorMessage(__($checkErrors));
                        }else{
                            $this->autifyDigitalHelper->addLog('Application was cancelled');
                            $this->messageManager->addErrorMessage(__('There is an error with the finance.'));
                        }

                        $this->autifyDigitalHelper->updateApplicationOrOrderStatus($currentOrder->getId(), 'Error in Application <br>' . 'Application Declined/Canceled Reason: <b>' . $checkErrors . '</b>', Order::STATE_CANCELED, '', 0);
                        if(
                            $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_enable') == 1 &&
                            !empty($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_url'))
                        ) {
                            $this->_redirect($this->_baseUrl.$this->autifyDigitalHelper->getConfig('autifydigital/v12finance/application_issue_url'));
                        } else {
                            $this->_redirect($this->_baseUrl.'checkout/cart');
                        }

                    } else {
                        $applicationId = $jsonDecodeArray['ApplicationId'];
                        $applicationUrl = $jsonDecodeArray['ApplicationFormUrl'];
                        $this->autifyDigitalHelper->addLog($jsonDecodeArray, true);
                        $this->applicationFactory->setApplicationStatus(1);//Acknowledged
                        $this->applicationFactory->setApplicationFormUrl($applicationUrl);
                        $this->applicationFactory->setApplicationGuid($jsonDecodeArray['ApplicationGuid']);
                        $this->applicationFactory->setFinanceApplicationId($applicationId);
                        $this->applicationFactory->setAuthorizationCode($jsonDecodeArray['AuthorisationCode']);
                        $this->applicationFactory->save();

                        if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/analytics_enable')) {
                            $this->autifyDigitalHelper->changeDatalayerStatus($applicationId);
                        }
                        
                        $paymentInfo = [];
                        $paymentInfo["method_title"] = $this->autifyDigitalHelper->getConfig("payment/v12finance/title");
                        $paymentInfo['application_id'] = $applicationId;
                        $paymentInfo['finance_name'] = $financeTitle;
                        $paymentInfo['finance_month'] = (int) $contractLength;
                        $paymentInfo['finance_apr'] = number_format((float)$financeTax, 2, '.', '');
                        $currentOrder->getPayment()->setAdditionalInformation($paymentInfo)->save();

                        $this->autifyDigitalHelper->updateApplicationOrOrderStatus($currentOrder->getId(), 'Application '  . $applicationId . ' sent to V12', $this->autifyDigitalHelper->getStatusConfig('acknowledged'), $applicationId, 1);
                        $this->_redirect($applicationUrl);

                    }
                }

            } else {
                $this->autifyDigitalHelper->addLog('Invalid V12 Application Request');
                $this->messageManager->addErrorMessage(__('Invalid V12 Application Request.'));
                $this->_redirect($this->_baseUrl);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->autifyDigitalHelper->addLog($e->getMessage());
            $this->autifyDigitalHelper->restoreQuote();
            //Check Email here
            if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/declined_email_enable') == 1){
                $this->mailHelper->sendDeclinedEmail($currentOrder->getIncrementId(), $currentOrder->getName(), $currentOrder->getCustomerEmail(), '');
            }
            $this->messageManager->addErrorMessage(__("Something Went Wrong. Please contact to support team."));
            $this->_redirect($this->_baseUrl);
        } catch (\Exception $e) {
            $this->autifyDigitalHelper->addLog($e->getMessage());
            $this->autifyDigitalHelper->restoreQuote();
            //Check Email here
            if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/declined_email_enable') == 1){
                $this->mailHelper->sendDeclinedEmail($currentOrder->getIncrementId(), $currentOrder->getName(), $currentOrder->getCustomerEmail(), '');
            }
            $this->messageManager->addErrorMessage(__("Something Went Wrong. Please contact to support team."));
            $this->_redirect($this->_baseUrl);
        }
    }


    private function saveFinance($currentOrder, $depositAmount, $totalAmountPayable, $v12FinanceId, $v12FinanceGuid, $contractLength)
    {
        $this->applicationFactory->setOrderId($currentOrder->getId())
        ->setOrderIncrementId($currentOrder->getIncrementId())
        ->setCustomerEmail($currentOrder->getCustomerEmail())
        ->setRetailerProductGuid($v12FinanceGuid)
        ->setRetailerId($v12FinanceId)
        ->setFinanceLength($contractLength)
        ->setOrderAmount($currentOrder->getGrandTotal())
        ->setFinanceAmount($currentOrder->getGrandTotal() - $depositAmount)
        ->setInterestAmount($totalAmountPayable - $currentOrder->getGrandTotal())
        ->setDepositAmount($depositAmount)
        ->setApplicationStatus(9)
        ->setLayerSent(0)
        ->setTotalAmountPayable($totalAmountPayable)
        ->setPendingEmailSent(0)
        ->setCreatedAt(date('Y-m-d H:i:s'))
        ->save();
    }
}

