<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;

class Data extends AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \AutifyDigital\V12Finance\Model\PriceOptionsFactory
     */
    protected $priceFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \AutifyDigital\V12Finance\Model\ApplicationFactory
     */
    protected $applicationFactory;

    /**
     * @var \Magento\Sales\Model\OrderNotifier
     */
    protected $orderNotifier;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timeZoneInterface;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    const V12_APPLICATION_URL = 'https://apply.v12finance.com/latest/retailerapi/SubmitApplication';

    const V12_APPLICATION_STATUS_URL = 'https://apply.v12finance.com/latest/retailerapi/CheckApplicationStatus';

    const V12_UPDATE_APPLICATION_URL = 'https://apply.v12finance.com/latest/retailerapi/UpdateApplication';

    const V12_GET_CARD_SUMMARY = 'https://apply.v12finance.com/latest/retailerapi/GetCardSummary';

    const V12_STATUS_ARRAY = array(
        '0' =>  'Error',
        '1' =>  'Acknowledged',
        '2' =>  'Referred',
        '3' =>  'Declined',
        '4' =>  'Accepted',
        '5' =>  'AwaitingFulfilment',
        '6' =>  'Payment Requested',
        '7' =>  'Payment Processed',
        '100' =>  'Cancelled'
    );

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \AutifyDigital\V12Finance\Model\PriceOptionsFactory $priceFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \AutifyDigital\V12Finance\Model\ApplicationFactory $applicationFactory
     * @param \Magento\Sales\Model\OrderNotifier $orderNotifier
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZoneInterface
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \AutifyDigital\V12Finance\Model\PriceOptionsFactory $priceFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \AutifyDigital\V12Finance\Model\ApplicationFactory $applicationFactory,
        \Magento\Sales\Model\OrderNotifier $orderNotifier,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZoneInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
        $this->_curl = $curl;
        $this->currencyFactory = $currencyFactory;
        $this->priceFactory = $priceFactory;
        $this->encryptorInterface = $encryptorInterface;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->applicationFactory = $applicationFactory;
        $this->orderNotifier = $orderNotifier;
        $this->_invoiceService = $invoiceService;
        $this->_transactionFactory = $transactionFactory;
        $this->invoiceSender = $invoiceSender;
        $this->addressConfig = $addressConfig;
        $this->timeZoneInterface = $timeZoneInterface;
        $this->stockRegistry = $stockRegistry;
        $this->moduleManager = $moduleManager;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrencyCode()
    {
        $currencyCode =  $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        return $this->currencyFactory->create()->load($currencyCode)->getCurrencySymbol();
    }

    /**
     * @param $url
     * @param array $postArray
     * @return array
     */
    public function callCurl($url, $postArray = array(), $analytics = false)
    {
        $this->addLog($this->jsonHelper->jsonEncode($postArray), true);
        if($analytics) {
            $headers = ["Content-Type" => "application/x-www-form-urlencoded"];
        } else {
            $headers = ["Content-Type" => "application/json", "Accept" => "application/json"];
        }

        $this->_curl->setHeaders($headers);
        $responseArray = array();
        try{
            if($analytics) {
                //Analytics
                $this->_curl->post($url, $postArray);
            } else {
                $this->_curl->post($url, $this->jsonHelper->jsonEncode($postArray));
            }
            $response = $this->_curl->getBody();
            $responseArray['status'] = 'success';
            if($analytics) {
                //Analytics
                $responseArray['response'] = $response;
            } else {
                $responseArray['response'] = $this->jsonHelper->jsonDecode($response);
            }
        } catch (\Exception $e) {
            $responseArray['status'] = 'error';
        }
        return $responseArray;
    }

    public function getApplicationFactory()
    {
        return $this->applicationFactory->create();
    }

    public function getAllApplication($startsFrom)
    {
        return $this->getApplicationFactory()
            ->getCollection()
            ->addFieldToFilter('application_status', array('in'=> array(1,2,4,5,6)))
            ->addFieldToFilter('created_at', array('gteq' => $startsFrom));
    }

    public function getAllPendingApplication($startsFrom)
    {
        return $this->getApplicationFactory()
            ->getCollection()
            ->addFieldToFilter('pending_email_sent', array('eq' => 0))
            ->addFieldToFilter('application_status', array('in'=> array(1,4)))
            ->addFieldToFilter('created_at', array('lteq' => $startsFrom));
    }

    public function getApplicationById($applicationId)
    {
        return $this->getApplicationFactory()
            ->getCollection()
            ->addFieldToFilter('finance_application_id', $applicationId)
            ->getFirstItem();
    }

    public function getApplicationByOrderId($orderId)
    {
        return $this->getApplicationFactory()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->getFirstItem();
    }

    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    public function callV12ClickAndCollect($getApplication, $applicationId)
    {
        $orderId = $getApplication->getOrderId();
        $order = $this->getOrder($orderId);
        $shippingMethod = $order->getShippingMethod();

        if($this->getClickAndCollect() === '1' && $shippingMethod === $this->getConfig('autifydigital/v12finance/click_and_collect_shipment_code_backend')) {
            $coreConfig = $this->getCoreConfig();

            $cashPriceRequest = array(
                "ApplicationId" => $applicationId,
                "Retailer" => array(
                    "AuthenticationKey" => $coreConfig['api_key'],
                    "RetailerGuid" => $coreConfig['retailer_guid'],
                    "RetailerId" => $coreConfig['retailer_id'],
                )
            );

            $this->addLog($cashPriceRequest, true);
            $curlCall = $this->callCurl(self::V12_GET_CARD_SUMMARY, $cashPriceRequest);
            $this->addLog($curlCall, true);

            if (isset($curlCall['status']) && $curlCall['status'] != 'error') :
                $response = $curlCall['response'];
                if($response['CardSummary']) {
                    $cardSummary = $response['CardSummary'];
                    $getApplication->setCardSummary($cardSummary)->save();
                    $commentArray = "";
                    $commentArray .= "---Start Autify Digital V12 Finance Card Summary History---&lt;br/&gt;";
                    $commentArray .= "V12 Card Summary Number: " . $cardSummary;
                    $commentArray .= "&lt;br/&gt;---End Autify Digital V12 Finance Card Summary History---";
                    $order->addStatusHistoryComment($commentArray, false);
                    $order->save();
                }
            endif;

        }

    }

    public function updateApplicationOrOrderStatus($orderId, $comment, $status = '', $applicationId ='', $v12Status = '', $response = false)
    {
        $order = $this->getOrder($orderId);
        $cancelledStatusArray = array(0, 100, 3);
        $pendingStatusArray = array(1, 2, 4);
        $processingStatusArray = array(5, 6, 7, 200);

        if($order->getStatus() !== 'complete') {
            if($v12Status === 5) {
                $order->setStatus('processing');
                //Reserve Stock
                if($this->getConfig('autifydigital/v12finance/outofstock_check') == '1') {
                    $this->reserveStock($order);
                }
            }elseif($status) {
                if($this->getConfig('autifydigital/v12finance/order_cancel') == 1) {
                    $order->setStatus($status);
                } else {
                    if(!in_array($v12Status, $cancelledStatusArray)) {
                        $order->setStatus($status);
                    }
                }
            }else{
            }

            if(in_array($v12Status, $pendingStatusArray)) {
                $order->setState(Order::STATE_NEW);//Pending
            }elseif (in_array($v12Status, $processingStatusArray)) {
                $order->setState(Order::STATE_PROCESSING);//processing
            }else{
            }
        }

        $commentArray = "";
        $commentArray .= "---Start Autify Digital V12 Finance Status History---&lt;br/&gt;";
        if($applicationId) {
            $commentArray .= "V12 Application Number: ". $applicationId ."&lt;br/&gt;";
        }

        if(in_array($v12Status, $cancelledStatusArray)) {
            if($this->getConfig('autifydigital/v12finance/order_cancel') == 1) {
                if($order->canCancel()) {
                    $order->cancel();
                }
                $this->restoreQuote();
                if($v12Status == '3' && $this->getConfig('autifydigital/v12finance/declined_email_enable') == 1){
                    ObjectManager::getInstance()->create(\AutifyDigital\V12Finance\Helper\Mail::class)->sendDeclinedEmail($order->getIncrementId(), $order->getName(), $order->getCustomerEmail(), $applicationId);
                }

                if($v12Status == '0' && $this->getConfig('autifydigital/v12finance/error_email_enable') == 1){
                    ObjectManager::getInstance()->create(\AutifyDigital\V12Finance\Helper\Mail::class)->sendErrorEmail($order->getIncrementId(), $order->getName(), $order->getCustomerEmail(), $applicationId);
                }

                if($v12Status == '100' && $this->getConfig('autifydigital/v12finance/cancelledemail_email_enable') == 1){
                    ObjectManager::getInstance()->create(\AutifyDigital\V12Finance\Helper\Mail::class)->sendCanceledEmail($order->getIncrementId(), $order->getName(), $order->getCustomerEmail(), $applicationId);
                }

                $order->setState(Order::STATE_CANCELED);//Cancel
            } else {
                $this->restoreQuote();
                $commentArray .= "MERCHANT ATTN: The application was declined, please cancel the order manually.";
            }
        }

        if($v12Status === 4 && $this->getConfig('autifydigital/v12finance/awaitingcontract_email_enable') == 1) {
            ObjectManager::getInstance()->create(\AutifyDigital\V12Finance\Helper\Mail::class)->sendAwaitingContractEmail($order->getIncrementId(), $order->getName(), $order->getCustomerEmail(), $applicationId);
        }

        if($v12Status === 4) {
            $commentArray .= 'Awaiting to sign the contract by the customer.&lt;br/&gt;';
        }

        $commentArray .= $comment . ' ---&lt;br/&gt;---End Autify Digital V12 Finance Status History---';

        $order->addStatusHistoryComment($commentArray, false);
        $order->save();

        if(in_array($v12Status, $processingStatusArray) && !$order->hasInvoices()) {
            $this->orderNotifier->notify($order);
            $this->generateInvoice($order);
        }

        if($response) {
            if(in_array($v12Status, $pendingStatusArray)) {
                return true;
            }elseif (in_array($v12Status, $processingStatusArray)) {
                return true;
            }elseif (in_array($v12Status, $cancelledStatusArray)) {
                //Need to get quote
                return false;
            }else{
                //Need to get quote

                return false;
            }
        }
    }

    /**
     * @param $product
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
    }

    /**
     * @param $product
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockRegistry()
    {
        return $this->stockRegistry;
    }

    /**
     * @param $order
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reserveStock($order)
    {
        if($this->checkMSIEnable()) {

            $this->getSkusByProductIds = ObjectManager::getInstance()->create(\Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface::class);
            $this->getProductTypesBySkus = ObjectManager::getInstance()->create(\Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface::class);
            $this->isSourceItemManagementAllowedForProductType = ObjectManager::getInstance()->create(\Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface::class);
            $this->itemsToSellFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory::class);
            $this->websiteRepository = ObjectManager::getInstance()->create(\Magento\Store\Api\WebsiteRepositoryInterface::class);
            $this->stockByWebsiteIdResolver = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface::class);
            $this->checkItemsQuantity = ObjectManager::getInstance()->create(\Magento\InventorySales\Model\CheckItemsQuantity::class);
            $this->salesEventFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory::class);
            $this->salesChannelFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory::class);
            $this->placeReservationsForSalesEvent = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface::class);
            $this->salesEventExtensionFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesEventExtensionFactory::class);

            $itemsById = $itemsBySku = $itemsToSell = [];

            foreach ($order->getItems() as $item) {
                if (!isset($itemsById[$item->getProductId()])) {
                    $itemsById[$item->getProductId()] = 0;
                }
                $itemsById[$item->getProductId()] += $item->getQtyOrdered();
            }
            $productSkus = $this->getSkusByProductIds->execute(array_keys($itemsById));
            $productTypes = $this->getProductTypesBySkus->execute($productSkus);

            foreach ($productSkus as $productId => $sku) {
                if (false === $this->isSourceItemManagementAllowedForProductType->execute($productTypes[$sku])) {
                    continue;
                }

                $itemsBySku[$sku] = (float)$itemsById[$productId];
                $itemsToSell[] = $this->itemsToSellFactory->create([
                    'sku' => $sku,
                    'qty' => -(float)$itemsById[$productId]
                ]);
            }

            $websiteId = (int)$order->getStore()->getWebsiteId();
            $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
            $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();

            $this->checkItemsQuantity->execute($itemsBySku, $stockId);

            /** @var \Magento\InventorySalesApi\Api\Data\SalesEventExtensionInterface */
            $salesEventExtension = $this->salesEventExtensionFactory->create([
                'data' => ['objectIncrementId' => (string)$order->getIncrementId()]
            ]);//New

            /** @var \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent */
            $salesEvent = $this->salesEventFactory->create([
                'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_ORDER_PLACED,
                'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => (string)$order->getEntityId()
            ]);
            $salesEvent->setExtensionAttributes($salesEventExtension);//New
            $salesChannel = $this->salesChannelFactory->create([
                'data' => [
                    'type' => \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                    'code' => $websiteCode
                ]
            ]);

            try {//New
                $this->placeReservationsForSalesEvent->execute($itemsToSell, $salesChannel, $salesEvent);
            } catch (\Exception $e) {//New
                //add compensation
                foreach ($itemsToSell as $item) {
                    $item->setQuantity(-(float)$item->getQuantity());
                }

                /** @var \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent */
                $salesEvent = $this->salesEventFactory->create([
                    'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_ORDER_PLACE_FAILED,
                    'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                    'objectId' => (string)$order->getEntityId()
                ]);
                $salesEvent->setExtensionAttributes($salesEventExtension);

                $this->placeReservationsForSalesEvent->execute($itemsToSell, $salesChannel, $salesEvent);
            }
        } else {
            foreach ($order->getItems() as $item) {
                $orderedQty = $item->getQtyOrdered();
                $stockRegistry = $this->getStockRegistry();
                $stockItem = $stockRegistry->getStockItemBySku($item->getSku());
                $stockQty = $stockItem->getQty();
                $stockQty -= $orderedQty;
                $stockItem->setQty($stockQty);
                $stockRegistry->updateStockItemBySku($item->getSku(), $stockItem);
            }
        }
    }

    /**
     * @param $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateInvoice($order)
    {
        //Generate Invoice
        $order = $this->getOrder($order->getId());
        if($order->canInvoice()) {
            $this->addLog('Invoice Generating Request: ' . $order->getId());
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->setTransactionId($order->getId());
            // $order->addStatusHistoryComment(__('Automatically INVOICED'), false);

            $transaction = $this->_transactionFactory->create()
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transaction->save();
            $this->invoiceSender->send($invoice);
            $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                ->setIsCustomerNotified(true)
                ->save();
        }
        //Generate Invoice
    }

    /**
     * @param $address
     * @return formatedaddress
     */
    public function getFormattedHtmlAddress($address)
    {
        return $this->addressConfig->getFormatByCode('html')->getRenderer()->renderArray($address);
    }

    /**
     * @param $orderId
     * @param $response
     */
    public function updateAddress($orderId, $response, $application)
    {
        
        if($application->getAddressUpdate() != '1') {
            $order = $this->getOrder($orderId);
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            $billingAddressId = $order->getBillingAddressId();
            $shippingAddressId = $order->getShippingAddressId();
            if($billingAddressId && $shippingAddressId) {
                $addressResponse = array();

                if(isset($response["Customer"]["Address"])) {

                    $addressResponse = $response["Customer"]["Address"];

                    $street = $city = $firstname = $lastname =  $postcode = $region = '';
                    $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
                    $oldBillingAddress =  $renderer->renderArray($billingAddress);
                    $oldShippingAddress =  $renderer->renderArray($shippingAddress);

                    if(isset($addressResponse["BuildingName"])) {
                        $street .= $addressResponse["BuildingName"];
                    }

                    if(isset($addressResponse["FlatNumber"])) {
                        $street .= ' ' . $addressResponse["FlatNumber"];
                    }

                    if(isset($addressResponse["BuildingNumber"])) {
                        $street .= ' ' . $addressResponse["BuildingNumber"];
                    }

                    if(isset($addressResponse["Street"])) {
                        $street .= ' ' . $addressResponse["Street"];
                    }

                    if(isset($response["Customer"]["FirstName"])){
                        $firstname = $response["Customer"]["FirstName"];
                    }

                    if(isset($response["Customer"]["LastName"])){
                        $lastname = $response["Customer"]["LastName"];
                    }

                    if(isset($addressResponse['Postcode'])){
                        $postcode = $addressResponse['Postcode'];
                    }

                    if(isset($addressResponse["TownOrCity"])){
                        $city = $addressResponse["TownOrCity"];
                    }

                    if(isset($addressResponse["County"])){
                        $region = $addressResponse["County"];
                    }

                    if(isset($response["Customer"]["MobileTelephone"])){
                        $telephone = $response["Customer"]["MobileTelephone"]["Code"] . $response["Customer"]["MobileTelephone"]["Number"];

                    }else{
                        $telephone = $billingAddress->getTelephone();
                    }

                    $changedAddress = array();
                    $billingAddressUpdate = ObjectManager::getInstance()->create('\Magento\Sales\Model\Order\Address')->load($billingAddressId);
                    $billingAddressUpdate->setFirstname($firstname)
                        ->setLastname($lastname)
                        ->setCompany("")
                        ->setStreet(trim($street))
                        ->setCity($city)
                        ->setPostcode($postcode)
                        ->setRegion($region)
                        ->setTelephone($telephone)
                        ->save();
                    $changedAddress[] = 1;

                    $clickAndCollect = $this->getClickAndCollect();
                    $clickAndCollectAddress = $this->getConfig('autifydigital/v12finance/click_and_collect_update_address');

                    $flagShippingAddress = true;
                    if($order->getShippingMethod() === $this->getConfig('autifydigital/v12finance/click_and_collect_shipment_code_backend')) {
                        if($clickAndCollect === '1' && $clickAndCollectAddress === '1') {
                            $flagShippingAddress = true;
                        }else{
                            $flagShippingAddress = false;
                        }
                    }

                    if($flagShippingAddress === true) {
                        $shippingAddressUpdate = ObjectManager::getInstance()->create('\Magento\Sales\Model\Order\Address')->load($shippingAddressId);
                        $shippingAddressUpdate->setFirstname($firstname)
                            ->setLastname($lastname)
                            ->setCompany("")
                            ->setStreet(trim($street))
                            ->setCity($city)
                            ->setPostcode($postcode)
                            ->setRegion($region)
                            ->setTelephone($telephone)
                            ->save();
                        $changedAddress[] = 1;
                    }

                    if($changedAddress && in_array(1, $changedAddress)) {
                        $application->setAddressUpdate(1)->save();
                        $commentArray = '';
                        $commentArray .= '---Start Autify Digital V12 Finance---&lt;br/&gt; Old Billing Address &lt;br/&gt;';
                        $commentArray .= $oldBillingAddress . ' ---&lt;br/&gt;';

                        if($flagShippingAddress === true) {
                            $commentArray .= 'Old Shipping Address &lt;br/&gt;';
                            $commentArray .= $oldShippingAddress . ' ---&lt;br/&gt;';
                        }

                        $commentArray .= '---End Autify Digital V12 Finance---';
                        $historyClass = ObjectManager::getInstance()->create(\Magento\Sales\Model\Order\Status\History::class);
                        $historyClass->setParentId($orderId)
                            ->setComment($commentArray)
                            ->setStatus($order->getStatus())
                            ->setEntityName('order')
                            ->save();
                    }
                }
            }
        }

    }

    /**
     * @param $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @return mixed
     */
    public function getClickAndCollect()
    {
        return $this->getConfig('autifydigital/v12finance/click_and_collect');
    }

    /**
     * @param $status
     * @return mixed
     */
    public function getStatusConfig($status) {
        return $this->getConfig('v12finance/finance_order_status/' . $status);
    }

    /**
     * @param $message
     * @param bool $array
     */
    public function addLog($message, $array = false)
    {
        $productMetaDataInterface = ObjectManager::getInstance()->create(\Magento\Framework\App\ProductMetadataInterface::class);
        $magentoVersion = $productMetaDataInterface->getVersion();
        if (version_compare($magentoVersion, '2.4.3', '>=')) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/v12autifydigitalfinance.log');
            $logger = new \Zend_Log();
        } else {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/v12autifydigitalfinance.log');
            $logger = new \Zend\Log\Logger();
        }
        $logger->addWriter($writer);
        if($array === true) {
            $logger->info(print_r($message, true));
        }else{
            $logger->info($message);
        }
    }

    /**
     * @param $string
     * @return string
     */
    public function decryptString($string)
    {
        return $this->encryptorInterface->decrypt($string);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getCurrentOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getOrder($orderId)
    {
        return $this->orderFactory->create()->load($orderId);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function restoreQuote()
    {
        $this->checkoutSession->restoreQuote();
        $quoteId = $this->getQuote()->getId();
        $quote = $this->quoteFactory->create()->load($quoteId);
        $quote->setData('v12_finance_enable', '0')
            ->setData('finance_updated_at', date('Y-m-d H:i:s'))
            ->save();
    }

    public function checkModuleEnable()
    {
        return $this->getConfig('payment/v12finance/active');
    }

    public function checkSalePriceProduct($product)
    {
        $orgprice = $product->getPrice();
        $specialprice = $product->getSpecialPrice();
        $specialfromdate = $product->getSpecialFromDate();
        $specialtodate = $product->getSpecialToDate();

        $todayDate = $this->getDateTime();
        $currentTime = strtotime($todayDate);

        if (!$specialprice)
          $specialprice = $orgprice;
        if ($specialprice< $orgprice) {
            if (
                (is_null($specialfromdate) && is_null($specialtodate)) ||
                ($currentTime >= strtotime($specialfromdate) && is_null($specialtodate)) ||
                ($currentTime <= strtotime($specialtodate) && is_null($specialfromdate)) ||
                ($currentTime >= strtotime($specialfromdate) && $currentTime <= strtotime($specialtodate))
            ) {
                return 1;
            }
        }
    }

    public function getCoreConfig()
    {
        $coreArray = array();
        $coreArray['api_key'] = $this->encryptorInterface->decrypt($this->getConfig('payment/v12finance/api_key'));
        $coreArray['retailer_id'] = $this->getConfig('payment/v12finance/retailer_id');
        $coreArray['retailer_guid'] = $this->encryptorInterface->decrypt($this->getConfig('payment/v12finance/retailer_guid'));
        $coreArray['click_and_collect'] = $this->getClickAndCollect();
        return $coreArray;
    }

    public function getCommonConfig()
    {
        $commonConfigArray = array();
        $commonConfigArray['min_order_total'] = $this->getConfig('payment/v12finance/min_order_total');
        $commonConfigArray['max_order_total'] = $this->getConfig('payment/v12finance/max_order_total');
        $commonConfigArray['min_deposit'] = $this->getConfig('payment/v12finance/min_deposit');
        $commonConfigArray['max_deposit'] = $this->getConfig('payment/v12finance/max_deposit');
        $commonConfigArray['min_percentage_value'] = $this->getConfig('payment/v12finance/finance_percentage_value');
        $commonConfigArray['max_percentage_value'] = $this->getConfig('payment/v12finance/finance_max_percentage_value');
        $commonConfigArray['default_finance_option'] = $this->getConfig('v12finance/financeoptions/default_finance_options');
        $commonConfigArray['default_sale_finance_options'] = $this->getConfig('v12finance/financeoptions/default_sale_finance_options');
        $commonConfigArray['default_sku_finance_options'] = $this->getConfig('v12finance/financeoptions/default_sku_finance_options');
        $commonConfigArray['default_price_finance_options'] = $this->getConfig('v12finance/financeoptions/default_price_finance_options');
        $commonConfigArray['sale_category_enable'] = $this->getConfig('v12finance/categoryfinance/enable_sale_category_finance');
        $commonConfigArray['sale_category_id'] = $this->getConfig('v12finance/categoryfinance/sale_category');
        $commonConfigArray['sku_enable_finance'] = $this->getConfig('autifydigital/v12finance/sku_enable');
        $commonConfigArray['sku_list'] = $this->getConfig('autifydigital/v12finance/sku_list');
        $commonConfigArray['price_based_finance'] = $this->getConfig('autifydigital/v12finance/price_based_finance');
        $commonConfigArray['sale_price_enable'] = $this->getConfig('autifydigital/v12finance/sale_price_enable');

        return $commonConfigArray;
    }

    public function getProductConfig()
    {
        $productConfigArray = $this->getCommonConfig();
        $productConfigArray['product_finance_title'] = $this->getConfig('v12finance/product_page/product_page_title');
        $productConfigArray['product_popup_finance_title'] = $this->getConfig('v12finance/product_page/product_page_text_header');
        $productConfigArray['product_page_button'] = $this->getConfig('v12finance/product_page/product_page_button');
        $productConfigArray['product_popup_para'] = $this->getConfig('v12finance/product_page/main_header_area');
        $productConfigArray['product_popup_terms'] = $this->getConfig('v12finance/product_page/finance_terms_conditions');
        return $productConfigArray;
    }

    public function getCheckoutConfig()
    {
        //CHeckout
        $checkoutConfigArray = $this->getCommonConfig();
        $checkoutConfigArray['checkout_page_payment_button'] = $this->getConfig('v12finance/checkout_page/checkout_page_payment_button');
        $checkoutConfigArray['checkout_page_message_time'] = $this->getConfig('v12finance/checkout_page/checkout_page_message_time');
        $checkoutConfigArray['checkout_page_message_text'] = $this->getConfig('v12finance/checkout_page/checkout_page_message_text');
        $checkoutConfigArray['checkout_page_billing_text'] = $this->getConfig('v12finance/checkout_page/checkout_page_billing_text');
        $checkoutConfigArray['checkout_financeamount_text'] = $this->getConfig('v12finance/checkout_page/checkout_financeamount_text');
        $checkoutConfigArray['checkout_couponcode_enable'] = $this->getConfig('autifydigital/v12finance/coupon_enable');
        return $checkoutConfigArray;
    }

    public function getAllDesignConfig()
    {
        $designArray = array();
        $designArray['font_color'] = $this->getConfig('autifydigitaldesign/design/font_color');
        $designArray['button_color'] = $this->getConfig('autifydigitaldesign/design/button_color');
        $designArray['button_hover_color'] = $this->getConfig('autifydigitaldesign/design/button_hover_color');
        $designArray['button_background_color'] = $this->getConfig('autifydigitaldesign/design/button_background_color');
        $designArray['button_background_hover_color'] = $this->getConfig('autifydigitaldesign/design/button_background_hover_color');
        $designArray['font_size'] = $this->getConfig('autifydigitaldesign/design/font_size');
        $designArray['custom_css'] = $this->getConfig('autifydigitaldesign/design/custom_css');
        return $designArray;
    }

    public function getGoogleMarketingConfig()
    {
        $marketingArray = array();
        $marketingArray['google_adwords_active'] = $this->getConfig('autifydigital/v12finance/adwords_enable');
        $marketingArray['google_adwords_conversion_id'] = $this->getConfig('autifydigital/v12finance/conversion_id');
        $marketingArray['google_adwords_conversion_language'] = $this->getConfig('autifydigital/v12finance/conversion_language');
        $marketingArray['google_adwords_conversion_format'] = $this->getConfig('autifydigital/v12finance/conversion_format');
        $marketingArray['google_adwords_conversion_color'] = $this->getConfig('autifydigital/v12finance/conversion_color');
        $marketingArray['google_adwords_conversion_label'] = $this->getConfig('autifydigital/v12finance/conversion_label');
        $marketingArray['google_analytics_active'] = $this->getConfig('autifydigital/v12finance/analytics_enable');
        return $marketingArray;
    }

    public function getProductFinanceEnableConfig()
    {
        return $this->getConfig('autifydigital/v12finance/product_finance_enable');
    }

    public function getArrangedFinanceOption($financeOptions)
    {
        $financeOptionsArray = '';
        if(is_array($financeOptions)) {
            //Return Finance Arranged Options
            foreach($financeOptions as $financeOption) :
                $financeExplodedString = explode('|', $financeOption ?? '');
                if(is_array($financeExplodedString) && isset($financeExplodedString[2])) {
                    $financeOptionsArray .= '<option value="' . $financeOption . '" data-finance-name="' . $financeExplodedString[2] . '" data-month="' . $financeExplodedString[4] . '" data-cf="' . $financeExplodedString[5] . '" data-interest="' . $financeExplodedString[3] . '" data-min-loan="' . $financeExplodedString[6] . '" data-max-loan="' . $financeExplodedString[7] . '"  >'.$financeExplodedString[2].'</option>';
                }
            endforeach;
        }
        return $financeOptionsArray;
    }


    public function getNormalFinanceOptions()
    {
        $financeOptions = $this->getConfig('v12finance/financeoptions/finance_options');
        $financeOptionsArray = explode(',', $financeOptions ?? '');
        return $this->getArrangedFinanceOption($financeOptionsArray);
    }

    public function getSaleFinanceOptions()
    {
        $financeOptions = $this->getConfig('v12finance/financeoptions/sale_finance_options');
        $financeOptionsArray = explode(',', $financeOptions ?? '');
        return $this->getArrangedFinanceOption($financeOptionsArray);
    }

    public function getSkuBasedFinanceOptions()
    {
        $financeOptions = $this->getConfig('v12finance/financeoptions/sku_finance_options');
        $financeOptionsArray = explode(',', $financeOptions ?? '');
        return $this->getArrangedFinanceOption($financeOptionsArray);
    }

    /**
     * @param $price
     * @return \Magento\Framework\DataObject
     */
    public function getPriceBasedFinanceOptions($price)
    {
        $priceModel = $this->priceFactory->create()->getCollection()->addFieldToFilter('price_from', array('lt' => $price))->addFieldToFilter('price_to', array('gteq' => $price))->addFieldToSelect('finance_options')->getFirstItem();
        $financeOptions = $priceModel->getFinanceOptions();
        $financeOptionsArray = explode(',', $financeOptions ?? '');
        return $this->getArrangedFinanceOption($financeOptionsArray);
    }

    /**
     * @return string
     */
    public function getDateTime()
    {
        return $this->timeZoneInterface->date()->format("Y-m-d H:i:s");
    }

    /**
     * @return string
     */
    public function formatDate($date, $format = \IntlDateFormatter::LONG )
    {
        return $this->timeZoneInterface->formatDate($date, $format);
    }

    public function getQuoteFactory()
    {
        return $this->quoteFactory->create();
    }

    public function changeAnalyticsStatus($applicationId)
    {
        $application = $this->getApplicationById($applicationId);
        $application->setData("analytics_sent", 1)->save();
    }

    public function changeDatalayerStatus($applicationId)
    {
        $application = $this->getApplicationById($applicationId);
        $application->setData("layer_sent", 1)->save();
    }

    public function checkMSIEnable()
    {
        if ($this->moduleManager->isEnabled('Magento_InventorySales')) {
            //the module is enabled
            return true;
        } else {
            //the module is disabled
            return false;
        }
    }

}

