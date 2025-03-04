<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

use Magento\Sales\Api\Data\OrderAddressExtensionInterface;
use Magento\Sales\Api\Data\OrderAddressExtensionInterfaceFactory;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class AddExtensionAttributesToOrderAddress
{
    /**
     * @var OrderAddressExtensionInterfaceFactory
     */
    private $extensionFactory;

    /**
     * AddExtensionAttributesToOrderAddress constructor.
     *
     * @param OrderAddressExtensionInterfaceFactory $orderAddressExtensionFactory
     */
    public function __construct(
        OrderAddressExtensionInterfaceFactory $orderAddressExtensionFactory
    ) {
        $this->extensionFactory = $orderAddressExtensionFactory;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Address\Collection $subject
     * @param \Magento\Framework\DataObject|\Magento\Sales\Model\Order\Address $item
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAddItem(
        \Magento\Sales\Model\ResourceModel\Order\Address\Collection $subject,
        \Magento\Framework\DataObject $item
    ) {
        // Return if address type is BILLING (only for SHIPPING)
        if ($item->getAddressType() !== \Magento\Sales\Model\Order\Address::TYPE_SHIPPING) {
            return [$item];
        }

        if (!$item instanceof \Magento\Sales\Api\Data\OrderAddressInterface) {
            return [$item];
        }

        /** @var OrderAddressExtensionInterface $extension */
        $extension = $item->getExtensionAttributes();
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        $extension->setDeliveryDay($item->getData(QueueDataInterface::DELIVERY_DAY_KEY));
        $extension->setDeliveryHoursFrom($item->getData(QueueDataInterface::DELIVERY_HOURS_FROM_KEY));
        $extension->setDeliveryMinutesFrom($item->getData(QueueDataInterface::DELIVERY_MINUTES_FROM_KEY));
        $extension->setDeliveryHoursTo($item->getData(QueueDataInterface::DELIVERY_HOURS_TO_KEY));
        $extension->setDeliveryMinutesTo($item->getData(QueueDataInterface::DELIVERY_MINUTES_TO_KEY));
        $extension->setDeliveryComment($item->getData(QueueDataInterface::DELIVERY_COMMENT_KEY));

        if ($extension->getDeliveryHoursFrom() && $extension->getDeliveryHoursTo()) {
            $extension->setDeliveryTime(
                implode(
                    QueueDataInterface::TIME_DELIMITER,
                    [
                        $extension->getDeliveryHoursFrom(),
                        $extension->getDeliveryMinutesFrom(),
                    ]
                )
                . '_' .
                implode(
                    QueueDataInterface::TIME_DELIMITER,
                    [
                        $extension->getDeliveryHoursTo(),
                        $extension->getDeliveryMinutesTo(),
                    ]
                )
            );
        } else {
            $extension->setDeliveryTime(null);
        }

        $item->setExtensionAttributes($extension);

        return [$item];
    }
}
