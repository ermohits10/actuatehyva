<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Plugin\Sales\Api\OrderManagement;

use AutifyDigital\V12Finance\Helper\Data;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class AppendReservationsAfterOrderPlacementPlugin
 * @package AutifyDigital\V12Finance\Plugin\Sales\Api\OrderManagement
 *
 * Disable reservation for V12 Finance Orders
 */
class AppendReservationsAfterOrderPlacementPlugin
{
    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var CheckItemsQuantity
     */
    private $checkItemsQuantity;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var SalesEventExtensionFactory;
     */
    private $salesEventExtensionFactory;

    protected $autifyDigitalHelper;

    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        Data $autifyDigitalHelper
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    /**
     * Add reservation before place order
     *
     * In case of error during order placement exception add compensation
     *
     * @param OrderManagementInterface $subject
     * @param callable $proceed
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlace(
        OrderManagementInterface $subject,
        callable $proceed,
        OrderInterface $order
    ): OrderInterface {

        if ($order->getPayment()->getMethodInstance()->getCode() == 'v12finance') {
            $order = $proceed($order);
            return $order;
        }

        if($this->autifyDigitalHelper->checkMSIEnable()) {

            $this->placeReservationsForSalesEvent = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface::class);
            $this->getSkusByProductIds = ObjectManager::getInstance()->create(\Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface::class);
            $this->salesChannelFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory::class);
            $this->salesEventFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory::class);
            $this->itemsToSellFactory = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory::class);
            $this->checkItemsQuantity = ObjectManager::getInstance()->create(\Magento\InventorySales\Model\CheckItemsQuantity::class);
            $this->stockByWebsiteIdResolver = ObjectManager::getInstance()->create(\Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface::class);
            $this->getProductTypesBySkus = ObjectManager::getInstance()->create(\Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface::class);
            $this->isSourceItemManagementAllowedForProductType = ObjectManager::getInstance()->create(\Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface::class);
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

            /** @var Magento\InventorySalesApi\Api\Data\SalesEventExtensionInterface */
            $salesEventExtension = $this->salesEventExtensionFactory->create([
                'data' => ['objectIncrementId' => (string)$order->getIncrementId()]
            ]);

            /** @var \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent */
            $salesEvent = $this->salesEventFactory->create([
                'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_ORDER_PLACED,
                'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => (string)$order->getEntityId()
            ]);
            $salesEvent->setExtensionAttributes($salesEventExtension);
            $salesChannel = $this->salesChannelFactory->create([
                'data' => [
                    'type' => \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                    'code' => $websiteCode
                ]
            ]);

            $this->placeReservationsForSalesEvent->execute($itemsToSell, $salesChannel, $salesEvent);

            try {
                $order = $proceed($order);
            } catch (\Exception $e) {
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

                throw $e;
            }

        } else {
            //Process with Non MSI
            foreach ($order->getItems() as $item) {
                $qty = $item->getQtyOrdered();
                $stockRegistry = $this->autifyDigitalHelper->getStockRegistry();
                $stockItem = $stockRegistry->getStockItemBySku($item->getSku());
                $stockQty = $stockItem->getQty();
                $stockQty -= $qty;
                $stockItem->setQty($stockQty);
                $stockRegistry->updateStockItemBySku($item->getSku(), $stockItem);
            }
        }


        return $order;
    }
}
