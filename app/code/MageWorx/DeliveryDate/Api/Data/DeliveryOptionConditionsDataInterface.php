<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api\Data;

interface DeliveryOptionConditionsDataInterface
{
    /**
     * Shipping method condition
     *
     * @return string|null
     */
    public function getShippingMethod(): ?string;

    /**
     * @param string|null $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setShippingMethod(?string $value): DeliveryOptionConditionsDataInterface;

    /**
     * Quote id condition
     *
     * @return int
     */
    public function getCartId(): int;

    /**
     * Quote id condition
     *
     * @param int $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setCartId(int $value): DeliveryOptionConditionsDataInterface;

    /**
     * Quote id condition for guest customer
     *
     * @return string|null
     */
    public function getGuestCartId(): ?string;

    /**
     * Quote id condition for guest customer
     *
     * @param string|null $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setGuestCartId(?string $value): DeliveryOptionConditionsDataInterface;

    /**
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getAddress(): \Magento\Quote\Api\Data\AddressInterface;

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setAddress(
        \Magento\Quote\Api\Data\AddressInterface $value
    ): DeliveryOptionConditionsDataInterface;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setStoreId(int $value): DeliveryOptionConditionsDataInterface;

    /**
     * @return int|null
     */
    public function getCustomerGroupId(): ?int;

    /**
     * @param int $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setCustomerGroupId(int $value): DeliveryOptionConditionsDataInterface;
}
