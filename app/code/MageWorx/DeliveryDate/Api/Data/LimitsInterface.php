<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api\Data;

interface LimitsInterface
{
    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return LimitsInterface
     */
    public function setMethod(string $method): LimitsInterface;

    /**
     * @return int|null
     */
    public function getEntityId();

    /**
     * @param int|null $entityId
     * @return LimitsInterface
     */
    public function setEntityId(int $entityId = null): LimitsInterface;

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     * @return LimitsInterface
     */
    public function setName(string $name = null): LimitsInterface;

    /**
     * @return string|null
     */
    public function getMethods();

    /**
     * @param string|null $methods
     * @return LimitsInterface
     */
    public function setMethods(string $methods = null): LimitsInterface;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $isActive
     * @return LimitsInterface
     */
    public function setIsActive(bool $isActive): LimitsInterface;

    /**
     * @return int|null
     */
    public function getSortOrder();

    /**
     * @param int|null $sortOrder
     * @return LimitsInterface
     */
    public function setSortOrder(int $sortOrder = null): LimitsInterface;

    /**
     * @return int|null
     */
    public function getFutureDaysLimit();

    /**
     * @param int|null $futureDaysLimit
     * @return LimitsInterface
     */
    public function setFutureDaysLimit(int $futureDaysLimit = null): LimitsInterface;

    /**
     * @return int|null
     */
    public function getStartDaysLimit();

    /**
     * @param int|null $startDaysLimit
     * @return LimitsInterface
     */
    public function setStartDaysLimit(int $startDaysLimit = null): LimitsInterface;

    /**
     * @return string|null
     */
    public function getActiveFrom();

    /**
     * @param string|null $activeFrom
     * @return LimitsInterface
     */
    public function setActiveFrom(string $activeFrom = null): LimitsInterface;

    /**
     * @return string|null
     */
    public function getActiveTo();

    /**
     * @param string|null $activeTo
     * @return LimitsInterface
     */
    public function setActiveTo(string $activeTo = null): LimitsInterface;

    /**
     * @return int|null
     */
    public function getShippingMethodsChoiceLimiter();

    /**
     * @param int|null $shippingMethodsChoiceLimiter
     * @return LimitsInterface
     */
    public function setShippingMethodsChoiceLimiter(int $shippingMethodsChoiceLimiter = null): LimitsInterface;

    /**
     * @return string|null
     */
    public function getWorkingDays();

    /**
     * @param string|null $workingDays
     * @return LimitsInterface
     */
    public function setWorkingDays(string $workingDays = null): LimitsInterface;

    /**
     * @return string|null
     */
    public function getCutOffTime();

    /**
     * @param string|null $cutOffTime
     * @return LimitsInterface
     */
    public function setCutOffTime(string $cutOffTime = null): LimitsInterface;

    /**
     * @return int|null
     */
    public function getQuotesScope();

    /**
     * @param int|null $quotesScope
     * @return LimitsInterface
     */
    public function setQuotesScope(int $quotesScope = null): LimitsInterface;

    /**
     * @return int[]
     */
    public function getStoreIds(): array;

    /**
     * @param int[] $storeIds
     * @return LimitsInterface
     */
    public function setStoreIds(array $storeIds): LimitsInterface;

    /**
     * @return int[]
     */
    public function getCustomerGroupIds(): array;

    /**
     * @param int[] $customerGroupIds
     * @return LimitsInterface
     */
    public function setCustomerGroupIds(array $customerGroupIds): LimitsInterface;

    /**
     * @return \MageWorx\DeliveryDate\Api\Data\DayLimitInterface[]
     */
    public function getDayLimits(): array;

    /**
     * @param \MageWorx\DeliveryDate\Api\Data\DayLimitInterface[] $dayLimits
     * @return LimitsInterface
     */
    public function setDayLimits(array $dayLimits): LimitsInterface;
}
