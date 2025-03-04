<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

use Magento\Quote\Api\Data\AddressExtensionInterfaceFactory;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterfaceFactory;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;

class AddExtensionAttributesToQuoteAddress
{
    /**
     * @var QueueManagerInterface
     */
    private $queueManager;

    /**
     * @var QueueDataInterfaceFactory
     */
    private $queueFactory;

    /**
     * @param QueueManagerInterface $queueManager
     * @param QueueDataInterfaceFactory $queueFactory
     */
    public function __construct(
        QueueManagerInterface     $queueManager,
        QueueDataInterfaceFactory $queueFactory
    ) {
        $this->queueManager = $queueManager;
        $this->queueFactory = $queueFactory;
    }

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Collection $subject
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Collection $result
     * @param \Magento\Framework\DataObject|\Magento\Quote\Model\Quote\Address $item
     * @return \Magento\Quote\Model\ResourceModel\Quote\Address\Collection
     */
    public function afterAddItem(
        \Magento\Quote\Model\ResourceModel\Quote\Address\Collection $subject,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Collection $result,
        \Magento\Framework\DataObject                               $item
    ) {
        // Return if address type is BILLING (only for SHIPPING)
        if ($item->getAddressType() === \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_BILLING) {
            return $result;
        }

        if (!$item instanceof \Magento\Quote\Api\Data\AddressInterface) {
            return $result;
        }

        $queue = null;

        if ($item->getId()) {
            $queue = $this->queueManager->getByQuoteAddressId((int)$item->getId());
        }

        if (!$queue) {
            $queue = $this->queueFactory->create();

            $deliveryDay         = $item->getData(QueueDataInterface::DELIVERY_DAY_KEY) ?? '';
            $deliveryHoursFrom   = $item->getData(QueueDataInterface::DELIVERY_HOURS_FROM_KEY) ?? '00';
            $deliveryHoursTo     = $item->getData(QueueDataInterface::DELIVERY_HOURS_TO_KEY) ?? '00';
            $deliveryMinutesFrom = $item->getData(QueueDataInterface::DELIVERY_MINUTES_FROM_KEY) ?? '00';
            $deliveryMinutesTo   = $item->getData(QueueDataInterface::DELIVERY_MINUTES_TO_KEY) ?? '00';
            $deliveryComment     = $item->getData(QueueDataInterface::DELIVERY_COMMENT_KEY) ?? '';
            $deliveryOptionId    = $item->getData('delivery_option_id') ?? 0;

            $queue->setDeliveryDay($deliveryDay)
                  ->setDeliveryHoursFrom($deliveryHoursFrom)
                  ->setDeliveryHoursTo($deliveryHoursTo)
                  ->setDeliveryMinutesFrom($deliveryMinutesFrom)
                  ->setDeliveryminutesTo($deliveryMinutesTo)
                  ->setDeliveryComment($deliveryComment)
                  ->setDeliveryOption($deliveryOptionId);
        }

        $this->queueManager->updateExtensionAttributesInQuoteAddress($item, $queue);

        return $result;
    }
}
