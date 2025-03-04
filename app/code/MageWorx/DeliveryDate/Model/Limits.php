<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model;

use MageWorx\DeliveryDate\Api\Data\LimitsInterface;

class Limits implements LimitsInterface
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var int
     */
    protected $entityId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $methods;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @var int
     */
    protected $sortOrder;

    /**
     * @var int
     */
    protected $futureDaysLimit;

    /**
     * @var int
     */
    protected $startDaysLimit;

    /**
     * @var string|null
     */
    protected $activeFrom;

    /**
     * @var string|null
     */
    protected $activeTo;

    /**
     * @var int
     */
    protected $shippingMethodsChoiceLimiter;

    /**
     * @var string
     */
    protected $workingDays;

    /**
     * @var string
     */
    protected $cutOffTime;

    /**
     * @var int
     */
    protected $quotesScope;

    /**
     * @var int[]
     */
    protected $storeIds;

    /**
     * @var int[]
     */
    protected $customerGroupIds;

    /**
     * @var \MageWorx\DeliveryDate\Api\Data\DayLimitInterface[]
     */
    protected $dayLimits;

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return LimitsInterface
     */
    public function setMethod(string $method): LimitsInterface
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     * @return LimitsInterface
     */
    public function setEntityId(int $entityId = null): LimitsInterface
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return LimitsInterface
     */
    public function setName(string $name = null): LimitsInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param string $methods
     * @return LimitsInterface
     */
    public function setMethods(string $methods = null): LimitsInterface
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return LimitsInterface
     */
    public function setIsActive(bool $isActive): LimitsInterface
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     * @return LimitsInterface
     */
    public function setSortOrder(int $sortOrder = null): LimitsInterface
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getFutureDaysLimit()
    {
        return $this->futureDaysLimit;
    }

    /**
     * @param int $futureDaysLimit
     * @return LimitsInterface
     */
    public function setFutureDaysLimit(int $futureDaysLimit = null): LimitsInterface
    {
        $this->futureDaysLimit = $futureDaysLimit;

        return $this;
    }

    /**
     * @return int
     */
    public function getStartDaysLimit()
    {
        return $this->startDaysLimit;
    }

    /**
     * @param int $startDaysLimit
     * @return LimitsInterface
     */
    public function setStartDaysLimit(int $startDaysLimit = null): LimitsInterface
    {
        $this->startDaysLimit = $startDaysLimit;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getActiveFrom()
    {
        return $this->activeFrom;
    }

    /**
     * @param string|null $activeFrom
     * @return LimitsInterface
     */
    public function setActiveFrom(string $activeFrom = null): LimitsInterface
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getActiveTo()
    {
        return $this->activeTo;
    }

    /**
     * @param string|null $activeTo
     * @return LimitsInterface
     */
    public function setActiveTo(string $activeTo = null): LimitsInterface
    {
        $this->activeTo = $activeTo;

        return $this;
    }

    /**
     * @return int
     */
    public function getShippingMethodsChoiceLimiter()
    {
        return $this->shippingMethodsChoiceLimiter;
    }

    /**
     * @param int $shippingMethodsChoiceLimiter
     * @return LimitsInterface
     */
    public function setShippingMethodsChoiceLimiter(int $shippingMethodsChoiceLimiter = null): LimitsInterface
    {
        $this->shippingMethodsChoiceLimiter = $shippingMethodsChoiceLimiter;

        return $this;
    }

    /**
     * @return string
     */
    public function getWorkingDays()
    {
        return $this->workingDays;
    }

    /**
     * @param string $workingDays
     * @return LimitsInterface
     */
    public function setWorkingDays(string $workingDays = null): LimitsInterface
    {
        $this->workingDays = $workingDays;

        return $this;
    }

    /**
     * @return string
     */
    public function getCutOffTime()
    {
        return $this->cutOffTime;
    }

    /**
     * @param string $cutOffTime
     * @return LimitsInterface
     */
    public function setCutOffTime(string $cutOffTime = null): LimitsInterface
    {
        $this->cutOffTime = $cutOffTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuotesScope()
    {
        return $this->quotesScope;
    }

    /**
     * @param int $quotesScope
     * @return LimitsInterface
     */
    public function setQuotesScope(int $quotesScope = null): LimitsInterface
    {
        $this->quotesScope = $quotesScope;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getStoreIds(): array
    {
        return $this->storeIds;
    }

    /**
     * @param int[] $storeIds
     * @return LimitsInterface
     */
    public function setStoreIds(array $storeIds): LimitsInterface
    {
        $this->storeIds = $storeIds;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getCustomerGroupIds(): array
    {
        return $this->customerGroupIds;
    }

    /**
     * @param int[] $customerGroupIds
     * @return LimitsInterface
     */
    public function setCustomerGroupIds(array $customerGroupIds): LimitsInterface
    {
        $this->customerGroupIds = $customerGroupIds;

        return $this;
    }

    /**
     * @return \MageWorx\DeliveryDate\Api\Data\DayLimitInterface[]
     */
    public function getDayLimits(): array
    {
        return $this->dayLimits;
    }

    /**
     * @param \MageWorx\DeliveryDate\Api\Data\DayLimitInterface[] $dayLimits
     * @return LimitsInterface
     */
    public function setDayLimits(array $dayLimits): LimitsInterface
    {
        $this->dayLimits = $dayLimits;

        return $this;
    }
}
