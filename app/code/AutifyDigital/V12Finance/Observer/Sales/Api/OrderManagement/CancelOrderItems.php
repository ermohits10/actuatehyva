<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */


namespace AutifyDigital\V12Finance\Observer\Sales\Api\OrderManagement;

use \AutifyDigital\V12Finance\Helper\Data;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Module\Manager;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\ObjectManager;


/**
 * Class CancelOrderItems
 * @package AutifyDigital\V12Finance\Observer\Sales\Api\OrderManagement
 */
class CancelOrderItems implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Configuration
     */
    protected $configuration;

    /**
     * @var StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var Processor
     */
    private $priceIndexer;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var GetItemsToCancelFromOrderItem
     */
    private $getItemsToCancelFromOrderItem;

    /**
     * @var SalesEventExtensionFactory;
     */
    private $salesEventExtensionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    private $autifyDigitalHelper;

    /**
     * @param Configuration $configuration
     * @param StockManagementInterface $stockManagement
     * @param Processor $priceIndexer
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param Data $autifyDigitalHelper
     * @param Manager $moduleManager
     */
    public function __construct(
        Configuration $configuration,
        StockManagementInterface $stockManagement,
        Processor $priceIndexer,
        WebsiteRepositoryInterface $websiteRepository,
        Data $autifyDigitalHelper,
        Manager $moduleManager
    ) {
        $this->configuration = $configuration;
        $this->stockManagement = $stockManagement;
        $this->priceIndexer = $priceIndexer;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        $this->moduleManager = $moduleManager;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        $orderItem = $observer->getEvent()->getItem();
        $order = $orderItem->getOrder();
        if ($order->getPayment()->getMethodInstance()->getCode() !== 'v12finance') {

            if ($this->moduleManager->isEnabled('Magento_InventorySales')) {
                $this->salesEventFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory::class);
                $this->placeReservationsForSalesEvent = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface::class);
                $this->salesChannelFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory::class);
                $this->getItemsToCancelFromOrderItem = ObjectManager::getInstance()->create(\Magento\InventorySales\Model\GetItemsToCancelFromOrderItem::class);
                $this->salesEventExtensionFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesEventExtensionFactory::class);;

                $itemsToCancel = $this->getItemsToCancelFromOrderItem->execute($orderItem);

                if (empty($itemsToCancel)) {
                    return;
                }

                $websiteId = $orderItem->getStore()->getWebsiteId();
                $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
                $salesChannel = $this->salesChannelFactory->create([
                    'data' => [
                        'type' => \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                        'code' => $websiteCode
                    ]
                ]);

                /** @var SalesEventExtensionInterface */
                $salesEventExtension = $this->salesEventExtensionFactory->create([
                    'data' => ['objectIncrementId' => (string)$orderItem->getOrder()->getIncrementId()]
                ]);

                $salesEvent = $this->salesEventFactory->create([
                    'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_ORDER_CANCELED,
                    'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                    'objectId' => (string)$orderItem->getOrderId()
                ]);
                $salesEvent->setExtensionAttributes($salesEventExtension);

                $this->placeReservationsForSalesEvent->execute($itemsToCancel, $salesChannel, $salesEvent);

                $this->priceIndexer->reindexRow($orderItem->getProductId());
            } else {
                /** @var \Magento\Sales\Model\Order\Item $item */
                $item = $observer->getEvent()->getItem();
                $children = $item->getChildrenItems();
                $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
                if ($item->getId() && $item->getProductId() && empty($children) && $qty && $this->configuration
                    ->getCanBackInStock()) {
                    $this->stockManagement->backItemQty($item->getProductId(), $qty, $item->getStore()->getWebsiteId());
                }
                $this->priceIndexer->reindexRow($item->getProductId());
            }
        }
    }
}
