<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface DeliveryManager
 *
 */
interface DeliveryManagerInterface
{
    /**
     * @param string $method
     * @param string|\DateTimeInterface $date
     * @param int $customerGroupId
     * @param int $storeId
     * @param bool $asArray
     *
     * @return DeliveryOptionInterface|array
     * @api
     */
    public function getDeliveryOptionForMethod(
        string $method,
        $date,
        int $customerGroupId,
        int $storeId,
        bool $asArray = false
    );

    /**
     * Find shipping method codes for which Delivery Option is available
     *
     * @param string|null|\DateTimeInterface $date
     * @param int|null $customerGroupId
     * @param int|null $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailableForMethods($date = null, ?int $customerGroupId = null, $storeId = 0): array;

    /**
     * Get Delivery Option suitable for all shipping methods
     *
     * @param \DateTimeInterface|null $date
     * @param int|null $customerGroupId
     * @param int $storeId
     * @param bool $asArray
     * @return DeliveryOptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDeliveryOptionForAllMethods(
        $date = null,
        ?int $customerGroupId = null,
        $storeId = 0,
        $asArray = false
    );

    /**
     * Get available limits for the quote (object).
     * In case the $advanced flag is set to "true" will return both available dates and time intervals and unavailable.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param bool $advanced
     * @return array
     */
    public function getAvailableLimitsForQuote(\Magento\Quote\Api\Data\CartInterface $quote, bool $advanced = false): array;

    /**
     * Get available limits for the quote by its id.
     * In case the $advanced flag is set to "true" will return both available dates and time intervals and unavailable.
     *
     * @param int $quoteId
     * @param bool $advanced - is need to return "unavailable" (reserved) dates
     * @return \MageWorx\DeliveryDate\Api\Data\LimitsInterface[]
     */
    public function getAvailableLimitsForQuoteById(int $quoteId, bool $advanced = false): array;

    /**
     * @param string $cartId
     * @param bool $advanced - is need to return "unavailable" (reserved) dates
     * @return \MageWorx\DeliveryDate\Api\Data\LimitsInterface[]
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getAvailableDeliveryDatesForGuestCart(string $cartId, bool $advanced = false): array;

    /**
     * Get delivery options limits by provided conditions
     *
     * @param DeliveryOptionConditionsInterface $deliveryOptionConditions
     * @param bool $advanced
     * @return \MageWorx\DeliveryDate\Api\Data\LimitsInterface[]
     * @api
     */
    public function getDeliveryOptionLimitsByConditions(
        DeliveryOptionConditionsInterface $deliveryOptionConditions,
        bool $advanced = false
    ): array;

    /**
     * Get suitable delivery option model by provided conditions
     *
     * @param DeliveryOptionConditionsInterface $deliveryOptionConditions
     * @param bool|null $advanced
     * @return DeliveryOptionInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDeliveryOptionByConditions(
        DeliveryOptionConditionsInterface $deliveryOptionConditions,
        ?bool $advanced = false
    ): ?DeliveryOptionInterface;

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return int
     * @throws \Exception
     */
    public function calculateDaysOffset(\Magento\Quote\Api\Data\CartInterface $quote): int;

    /**
     * Set offset days
     *
     * @param int $days
     * @return DeliveryManagerInterface
     */
    public function setDaysOffset($days = 0): \MageWorx\DeliveryDate\Api\DeliveryManagerInterface;

    /**
     * Get current days offset
     *
     * @return int
     */
    public function getDaysOffset(): int;

    /**
     * The code of shipping method by which we should filter result of delivery dates calculations.
     *
     * @return string
     */
    public function getShippingMethodFilter(): string;

    /**
     * The code of shipping method by which we should filter result of delivery dates calculations.
     *
     * @param string $value
     * @return DeliveryManagerInterface
     */
    public function setShippingMethodFilter(string $value): \MageWorx\DeliveryDate\Api\DeliveryManagerInterface;

    /**
     * Get flag by which advanced data could be added to the response or not
     *
     * @return bool
     */
    public function getIsAdvanced(): bool;

    /**
     * Set flag which adds advanced data to the response
     *
     * @param bool $value
     * @return \MageWorx\DeliveryDate\Api\DeliveryManagerInterface
     */
    public function setIsAdvanced(bool $value): \MageWorx\DeliveryDate\Api\DeliveryManagerInterface;

    /**
     * Non working days selected in he products from the current cart
     *
     * @return array|string[]
     */
    public function getNonWorkingDaysFromProductsInCart(): array;

    /**
     * Set actual quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \MageWorx\DeliveryDate\Api\DeliveryManagerInterface
     */
    public function setQuote(\Magento\Quote\Api\Data\CartInterface $quote): \MageWorx\DeliveryDate\Api\DeliveryManagerInterface;

    /**
     * Get actual quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuote(): ?\Magento\Quote\Api\Data\CartInterface;

    /**
     * Check is all products has disabled setting "Allow Delivery Date"
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function deliveryTimeDisabledOnAllProducts(\Magento\Quote\Api\Data\CartInterface $quote): bool;

    /**
     * Convert regular quote to conditions
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param array|null $additionalData
     * @return DeliveryOptionConditionsInterface
     */
    public function convertQuoteToConditions(
        \Magento\Quote\Api\Data\CartInterface $quote,
        ?array $additionalData = []
    ): DeliveryOptionConditionsInterface;
}
