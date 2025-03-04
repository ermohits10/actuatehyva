<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderAddressExtensionInterfaceFactory;

class TransferInfoFromQuoteAddressToOrderAddress implements ObserverInterface
{
    /**
     * @var OrderAddressExtensionInterfaceFactory
     */
    private $orderAddressExtensionInterfaceFactory;

    /**
     * @var array
     */
    private $dataKeys;

    /**
     * TransferInfoFromQuoteAddressToOrderAddress constructor.
     *
     * @param OrderAddressExtensionInterfaceFactory $orderAddressExtensionInterfaceFactory
     * @param array $dataKeys
     */
    public function __construct(
        OrderAddressExtensionInterfaceFactory $orderAddressExtensionInterfaceFactory,
        array $dataKeys = []
    ) {
        $this->orderAddressExtensionInterfaceFactory = $orderAddressExtensionInterfaceFactory;
        $this->dataKeys                              = $dataKeys;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $observer->getAddress();

        if (!$quote || !$order) {
            return;
        }

        $quoteShippingAddress = $address ?? $quote->getShippingAddress();
        $orderShippingAddress = $order->getShippingAddress();

        if (!$quoteShippingAddress || !$orderShippingAddress) {
            return;
        }

        $quoteShippingAddressExtensionAttributes = $quoteShippingAddress->getExtensionAttributes();
        $orderShippingAddressExtensionAttributes = $orderShippingAddress->getExtensionAttributes();
        if (!$quoteShippingAddressExtensionAttributes) {
            return;
        }

        if (!$orderShippingAddressExtensionAttributes) {
            /** @var \Magento\Sales\Api\Data\OrderAddressExtensionInterface $orderShippingAddressExtensionAttributes */
            $orderShippingAddressExtensionAttributes = $this->orderAddressExtensionInterfaceFactory->create();
        }

        foreach ($this->dataKeys as $key) {
            $value          = null;
            $mainMethodPart = implode('', array_map('ucfirst', explode('_', $key)));
            $getter         = 'get' . $mainMethodPart;
            $setter         = 'set' . $mainMethodPart;

            if (method_exists($quoteShippingAddressExtensionAttributes, $getter) &&
                method_exists($orderShippingAddressExtensionAttributes, $setter)
            ) {
                $value = $quoteShippingAddressExtensionAttributes->{$getter}();
                $orderShippingAddressExtensionAttributes->{$setter}($value);
            }
        }

        $orderShippingAddressExtensionAttributes->setQuoteAddressId($quoteShippingAddress->getId());

        $orderShippingAddress->setExtensionAttributes($orderShippingAddressExtensionAttributes);
    }
}
