<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface DeliveryOptionInterface
 *
 */
interface DeliveryOptionInterface extends Data\DeliveryOptionDataInterface
{
    /**
     * Maximum allowed future days (limit in days for the calculations)
     */
    const MAX_DAYS_CALCULATION = 365;

    /**
     * Default time when time is not set in a time-limit
     */
    const MIN_TIME_IN_DAY = 0;

    /**
     * Default time when time is not set in a time-limit
     */
    const MAX_TIME_IN_DAY = 24;

    /**
     * Id for the ALL store views option
     */
    const ALL_STORE_VIEWS_ID = '0';

    /**
     * Returns limit for the specified day.
     * If $anyway flag set to true returns limit (if available) for the holidays and non working days.
     * This flag has been added for the quotes preview purposes, when day is a holiday/non-working but limit
     * has been set manually or when this queue was created before settings has changed.
     *
     * @param $day
     * @param bool $anyway
     * @return array
     */
    public function getLimit($day, $anyway = false);

    /**
     * @param \DateTimeInterface|null $date
     * @param int $daysOffset
     * @param int $maxCalculationDays
     * @return DeliveryOptionInterface
     */
    public function fillDayLimits(
        \DateTimeInterface $date = null,
        $daysOffset = 0,
        $maxCalculationDays = 0
    );

    /**
     * Fill delivery option with day limits having disabled dates marked
     *
     * @param \DateTimeInterface|null $date
     * @param int $daysOffset
     * @param int $maxCalculationDays
     * @return DeliveryOptionInterface
     * @throws LocalizedException
     */
    public function fillDayLimitsWithDisabled(
        \DateTimeInterface $date = null,
        $daysOffset = 0,
        $maxCalculationDays = 0
    );

    /**
     * Set day limits calculated
     *
     * @param array $array
     * @return DeliveryOptionInterface
     */
    public function setDayLimits(array $array);

    /**
     * Check is a time limit available for the specified day
     *
     * @param \DateTimeInterface $day
     * @param $timeLimitTemplate
     * @return bool
     */
    public function isTimeLimitAvailable(\DateTimeInterface $day, array $timeLimitTemplate);

    /**
     * Get count of already reserved quotes for specified time limit
     *
     * @param \DateTimeInterface $day
     * @param array $timeLimitTemplate
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getReservedQuotesForTimeLimit(\DateTimeInterface $day, array $timeLimitTemplate);

    /**
     * Get count of already reserved quotes for specified day
     *
     * @param \DateTimeInterface $day
     * @param bool $ignoreGeneralQueue
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getReservedQuotesForDay(\DateTimeInterface $day, $ignoreGeneralQueue = false);

    /**
     * Get all available day limits (calculated from today)
     * Returns limits for each available day from today to the future, limited by the `getFutureDaysLimit()` days
     *
     * @return array
     */
    public function getDayLimits();

    /**
     * @param array $data
     * @return DeliveryOptionInterface
     */
    public function addData(array $data);

    /**
     * Validate model data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[] - return true if validation passed successfully. Array with errors description otherwise
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateData(\Magento\Framework\DataObject $dataObject);

    /**
     * Filter working days: leave only real days.
     * For the list of valid days
     *
     * @param array $workingDays
     * @return array
     * @see \MageWorx\DeliveryDate\Model\Source\WorkingDays
     *
     */
    public function filterWorkingDays(array $workingDays);

    /**
     * Set start date from when delivery calculation available
     *
     * @param \DateTimeInterface|null $date
     * @return DeliveryOptionInterface
     */
    public function setStartDate(\DateTimeInterface $date = null): DeliveryOptionInterface;

    /**
     * Set end date to when delivery calculation available
     *
     * @param \DateTimeInterface|null $date
     * @return DeliveryOptionInterface
     */
    public function setEndDate(\DateTimeInterface $date = null): DeliveryOptionInterface;

    /**
     * Get start date from when delivery calculation available
     *
     * @return \DateTimeInterface|null
     */
    public function getStartDate(): ?\DateTimeInterface;

    /**
     * Get end date to when delivery calculation available
     *
     * @return \DateTimeInterface|null
     */
    public function getEndDate(): ?\DateTimeInterface;

    /**
     * Check is the Delivery Option applicable for the Shipping Method
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isApplicableForShippingMethod(string $shippingMethod): bool;
}
