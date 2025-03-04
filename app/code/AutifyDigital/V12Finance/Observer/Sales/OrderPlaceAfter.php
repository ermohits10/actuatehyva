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

use AutifyDigital\V12Finance\Helper\Data;
use Magento\Framework\App\ObjectManager;

/**
 * Class OrderPlaceAfter
 * @package AutifyDigital\V12Finance\Observer\Sales
 */
class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment()->getMethodInstance()->getCode();
        if($payment === 'v12finance') {
            $order->setCanSendNewEmailFlag(false);
            $order->setSendEmail(false);
            $order->save();

            //Process with Non MSI
            $this->autifyDigitalHelper = ObjectManager::getInstance()->create(\AutifyDigital\V12Finance\Helper\Data::class);

            if(!$this->autifyDigitalHelper->checkMSIEnable()) {
                foreach ($order->getItems() as $item) {
                    $qty = $item->getQtyOrdered();
                    $stockRegistry = $this->autifyDigitalHelper->getStockRegistry();
                    $stockItem = $stockRegistry->getStockItemBySku($item->getSku());
                    $stockQty = $stockItem->getQty();
                    $stockQty += $qty;
                    $stockItem->setQty($stockQty);
                    $stockRegistry->updateStockItemBySku($item->getSku(), $stockItem);
                }    
            }
            

        }
    }
}

