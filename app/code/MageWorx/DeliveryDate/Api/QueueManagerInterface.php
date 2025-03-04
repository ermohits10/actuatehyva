<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Exceptions\QueueException;

/**
 * Interface QueueManagerInterface
 *
 */
interface QueueManagerInterface
{
    /**
     * @param int $addressId
     * @return \MageWorx\DeliveryDate\Api\Data\QueueDataInterface|null
     */
    public function getByQuoteAddressId($addressId): ?\MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

    /**
     * @param int $addressId
     * @return \MageWorx\DeliveryDate\Api\Data\QueueDataInterface|null
     */
    public function getByOrderAddressId($addressId): ?\MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

    /**
     * @param \MageWorx\DeliveryDate\Api\Data\QueueDataInterface $queue
     * @param array $data
     * @return QueueManagerInterface
     * @throws QueueException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateQueue(
        \MageWorx\DeliveryDate\Api\Data\QueueDataInterface $queue,
        array                                              $data = []
    ): QueueManagerInterface;

    /**
     * Check: is a selected delivery time is over a limit?
     *
     * @param DeliveryOptionInterface $deliveryOption
     * @param \DateTime $day
     * @param int $hoursFrom
     * @param int $hoursTo
     * @param int $minutesFrom
     * @param int $minutesTo
     * @return bool
     */
    public function isLimitExceeded(
        DeliveryOptionInterface $deliveryOption,
        \DateTime               $day,
        $hoursFrom = 0,
        $hoursTo = 0,
        $minutesFrom = 0,
        $minutesTo = 0
    ): bool;

    /**
     * Return selected delivery date for specified cart
     *
     * @param int $cartId
     * @return \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface|null
     */
    public function getSelectedDeliveryDateByCartId(
        int $cartId
    ): ?\MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;

    /**
     * Return selected delivery date for specified guest cart
     *
     * @param string $cartId
     * @return \MageWorx\DeliveryDate\Api\Data\QueueDataInterface|null
     */
    public function getSelectedDeliveryDateByGuestCartId(
        string $cartId
    ): ?\MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

    /**
     * Set delivery date and time for cart
     *
     * @param int $cartId
     * @param \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface $deliveryDateData
     * @return \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface|null
     * @throws LocalizedException|QueueException
     */
    public function setDeliveryDateForCart(
        int                                                       $cartId,
        \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface $deliveryDateData,
        ?AddressInterface                                         $address
    ): ?\MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;

    /**
     * Set delivery date and time for guest cart
     *
     * @param string $cartId
     * @param \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface $deliveryDateData
     * @return \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface|null
     * @throws LocalizedException|QueueException
     */
    public function setDeliveryDateForGuestCart(
        string                                                    $cartId,
        \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface $deliveryDateData,
        ?AddressInterface                                         $address
    ): ?\MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;

    /**
     * @param AddressInterface $address
     * @param QueueDataInterface $queue
     * @return QueueManagerInterface
     */
    public function updateExtensionAttributesInQuoteAddress(
        AddressInterface $address,
        QueueDataInterface $queue
    ): QueueManagerInterface;

    /**
     * Remove queue for quote address
     *
     * @param int $addressId
     * @return void
     * @throws LocalizedException
     */
    public function cleanQueueByQuoteAddressId(int $addressId): void;

    /**
     * Clean queue data linked with quote address; Clean queue data in address;
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @return void
     * @throws LocalizedException
     */
    public function cleanDeliveryDateDataByQuoteAddress(\Magento\Quote\Api\Data\AddressInterface $shippingAddress): void;
}
