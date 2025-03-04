<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model;

use DateTimeInterface;
use JsonSerializable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryOptionDataInterface;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;
use MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use MageWorx\DeliveryDate\Model\Source\Month as MonthSourceModel;
use MageWorx\DeliveryDate\Model\Source\WorkingDays;

class DeliveryOption extends AbstractModel implements DeliveryOptionInterface, JsonSerializable
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var array
     */
    private $holidays;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var array
     */
    private $nonWorkingDays = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DeliveryManagerInterface
     */
    private $deliveryManager;

    /**
     * @var \DateTimeInterface|null
     */
    protected $startDate;

    /**
     * @var \DateTimeInterface|null
     */
    protected $endDate;

    /**
     * DeliveryOption constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param QueueRepositoryInterface $queueRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Helper $helper
     * @param StoreManagerInterface $storeManager
     * @param DeliveryManagerInterface $deliveryManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        QueueRepositoryInterface $queueRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Helper $helper,
        StoreManagerInterface $storeManager,
        DeliveryManagerInterface $deliveryManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dateTime              = $dateTime;
        $this->timezone              = $timezone;
        $this->queueRepository       = $queueRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helper                = $helper;
        $this->storeManager          = $storeManager;
        $this->deliveryManager       = $deliveryManager;
    }

    /**
     * Processing object before save data
     *
     * @return AbstractModel|DeliveryOption
     */
    public function beforeSave()
    {
        $this->prepareHolidays();

        if ($this->getFutureDaysLimit() > self::MAX_DAYS_CALCULATION) {
            $this->setFutureDaysLimit(self::MAX_DAYS_CALCULATION);
        }

        return parent::beforeSave();
    }

    /**
     * Prepares holidays: set them as a recurring in case it's needed by logic
     *
     * @return $this
     */
    public function prepareHolidays()
    {
        $holidaysSerialized = $this->getHolidaysSerialized();
        $wasSerialized      = false;
        if (is_string($holidaysSerialized)) {
            $holidays      = json_decode($holidaysSerialized, true);
            $wasSerialized = true;
        } elseif (is_array($holidaysSerialized)) {
            $holidays = $holidaysSerialized;
        } else {
            $holidays = [];
        }

        foreach ($holidays as $index => $holiday) {
            if (empty($holiday['day'])) {
                unset($holidays[$index]);
                continue;
            }
            if ($holiday['month'] === MonthSourceModel::MONTH_ANY) {
                $holiday['recurring'] = 1;
            }
            if (empty($holiday['year'])) {
                $holiday['recurring'] = 1;
            }
        }

        if ($wasSerialized) {
            $holidays = json_encode($holidays);
        }

        $this->setHolidaysSerialized($holidays);

        return $this;
    }

    /**
     * @return string
     */
    public function getHolidaysSerialized()
    {
        return $this->getData(static::KEY_HOLIDAYS_SERIALIZED);
    }

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setHolidaysSerialized($value)
    {
        return $this->setData(static::KEY_HOLIDAYS_SERIALIZED, $value);
    }

    /**
     * Returns limit for the specified day (any)
     *
     * @param $day
     * @param bool $anyway
     * @return array
     */
    public function getLimit($day, $anyway = false)
    {
        return $this->getTemplateForDate($day, $anyway);
    }

    /**
     * @param \DateTime $date
     * @param bool $anyway
     * @return array
     */
    private function getTemplateForDate($date, $anyway = false)
    {
        $dayOfWeekAsAString = mb_strtolower($date->format('l'));
        $limitTemplates     = $this->getLimitsSerialized();

        $workingDays = $this->getWorkingDays();
        if (!in_array($dayOfWeekAsAString, $workingDays) && !$anyway) {
            return [];
        }

        if (!$dayOfWeekAsAString) {
            return [];
        }

        if ($this->getQuotesScope() == static::QUOTES_SCOPE_UNLIMITED) {
            $dayTemplate                 = $limitTemplates[static::LIMITS_GENERAL];
            $dayTemplate['daily_quotes'] = static::QUOTES_UNLIMITED;
            $dayTemplate['active']       = true;
        } elseif ($this->getQuotesScope() == static::QUOTES_SCOPE_PER_DAY) {
            $dayTemplate           = $limitTemplates[static::LIMITS_GENERAL];
            $dayTemplate['active'] = true;
        } else {
            if (empty($limitTemplates[$dayOfWeekAsAString])) {
                return [];
            }

            $dayTemplate = $limitTemplates[$dayOfWeekAsAString];
            if (empty($dayTemplate['active']) || !$dayTemplate['active']) {
                return [];
            }
        }

        return $dayTemplate;
    }

    /**
     * @return array
     */
    public function getLimitsSerialized()
    {
        return $this->getData(static::KEY_LIMITS_SERIALIZED);
    }

    /**
     * Get working days for which delivery option is available
     *
     * @return null|array
     */
    public function getWorkingDays()
    {
        $workingDays    = $this->getData(static::KEY_WORKING_DAYS);
        $nonWorkingDays = $this->getNonWorkingDaysByCart();
        if (!$workingDays) {
            $workingDays = WorkingDays::getDaysArray();
        }

        if (!is_array($workingDays)) {
            $workingDays = explode(',', $workingDays);
        }

        $workingDays         = array_diff($workingDays, $nonWorkingDays);
        $workingDaysFiltered = $this->filterWorkingDays($workingDays);

        return $workingDaysFiltered;
    }

    /**
     * Filter working days: leave only real days.
     * For the list of valid days
     *
     * @param array $workingDays
     * @return array
     * @see \MageWorx\DeliveryDate\Model\Source\WorkingDays
     *
     */
    public function filterWorkingDays(array $workingDays)
    {
        $workingDaysSourceModel = $this->helper->getWorkingDaysSourceModel();
        $realWorkingDays        = $workingDaysSourceModel->__toArray();
        $workingDaysFiltered    = array_filter(
            $workingDays,
            function ($value) use ($realWorkingDays) {
                return in_array($value, $realWorkingDays);
            }
        );

        return $workingDaysFiltered;
    }

    /**
     * Get quotes scope
     *
     * @return int
     * @see \MageWorx\DeliveryDate\Model\Source\QuotesScope
     *
     */
    public function getQuotesScope()
    {
        return $this->getData(static::KEY_QUOTES_SCOPE);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(static::KEY_NAME);
    }

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setName($value)
    {
        return $this->setData(static::KEY_NAME, $value);
    }

    /**
     * @param array $methods
     * @return DeliveryOptionDataInterface
     */
    public function setMethods(array $methods)
    {
        return $this->setData(static::KEY_METHODS, $methods);
    }

    /**
     * Returns sort order of the delivery option
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData(static::KEY_SORT_ORDER);
    }

    /**
     * @param int $sortOrder
     * @return DeliveryOptionDataInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(static::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * Returns date and time from witch this delivery option was active
     *
     * @return DateTimeInterface
     * @throws \Exception
     */
    public function getActiveFrom()
    {
        $day = $this->getData(static::KEY_ACTIVE_FROM);
        if (!$day instanceof DateTimeInterface) {
            $day = new \DateTime((string)$day);
            $this->setActiveFrom($day);
        }

        return $day;
    }

    /**
     * @param DateTimeInterface $from
     * @return DeliveryOptionDataInterface
     */
    public function setActiveFrom(DateTimeInterface $from)
    {
        return $this->setData(static::KEY_ACTIVE_FROM, $from);
    }

    /**
     * Returns array of customer groups for witch this delivery option was active
     *
     * @return int[]
     */
    public function getCustomerGroups()
    {
        return $this->getData(static::KEY_CUSTOMER_GROUPS);
    }

    /**
     * @param int[] $groupIds
     * @return DeliveryOptionDataInterface
     */
    public function setCustomerGroups($groupIds)
    {
        return $this->setData(static::KEY_CUSTOMER_GROUPS, $groupIds);
    }

    /**
     * @param string[] $storeIds
     * @return DeliveryOptionDataInterface
     */
    public function setStoreIds($storeIds)
    {
        return $this->setData(static::KEY_STORE_IDS, $storeIds);
    }

    /**
     * Is Active?
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getData(static::KEY_IS_ACTIVE);
    }

    /**
     * @param bool $flag
     * @return DeliveryOptionDataInterface
     */
    public function setIsActive($flag)
    {
        return $this->setData(static::KEY_IS_ACTIVE, $flag);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return $this->getData();
    }

    /**
     * @param int $value
     * @return DeliveryOptionDataInterface
     */
    public function setFutureDaysLimit($value)
    {
        return $this->setData(static::KEY_FUTURE_DAYS_LIMITS, $value);
    }

    /**
     * @param int $value
     * @return DeliveryOptionDataInterface
     */
    public function setStartDaysLimit($value)
    {
        return $this->setData(static::KEY_START_DAYS_LIMITS, $value);
    }

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setLimitsSerialized($value)
    {
        return $this->setData(static::KEY_LIMITS_SERIALIZED, $value);
    }

    /**
     * Check is a time limit available for the specified day
     *
     * @param DateTimeInterface $day
     * @param array $timeLimitTemplate
     * @return bool
     */
    public function isTimeLimitAvailable(DateTimeInterface $day, array $timeLimitTemplate)
    {
        if ($this->getQuotesScope() == static::QUOTES_SCOPE_UNLIMITED) {
            return true;
        }

        if (empty($timeLimitTemplate['quote_limit'])) {
            return true;
        }

        $quoteLimit   = (int)$timeLimitTemplate['quote_limit'];
        $searchResult = $this->getReservedQuotesForTimeLimit($day, $timeLimitTemplate);
        $totalCount   = $searchResult->getTotalCount();

        if ($totalCount >= $quoteLimit) {
            return false;
        }

        return true;
    }

    /**
     * Get count of already reserved quotes for specified time limit
     *
     * @param DateTimeInterface $day
     * @param array $timeLimitTemplate
     * @return SearchResultsInterface
     */
    public function getReservedQuotesForTimeLimit(DateTimeInterface $day, array $timeLimitTemplate)
    {
        $fromToParts = $this->helper->parseFromToPartsFromTimeLimitTemplate($timeLimitTemplate);

        $this->searchCriteriaBuilder->addFilter('delivery_day', $day->format('Y-m-d'), 'eq');
        $this->searchCriteriaBuilder->addFilter('delivery_hours_from', $fromToParts['from']['hours'], 'eq');
        $this->searchCriteriaBuilder->addFilter('delivery_minutes_from', $fromToParts['from']['minutes'], 'eq');
        $this->searchCriteriaBuilder->addFilter('delivery_hours_to', $fromToParts['to']['hours'], 'eq');
        $this->searchCriteriaBuilder->addFilter('delivery_minutes_to', $fromToParts['to']['minutes'], 'eq');

        if (!empty($this->getMethods())) {
            $this->searchCriteriaBuilder->addFilter(
                'shipping_method',
                $this->getMethods(),
                'in'
            );
        }

        // Filter by a store when delivery option not for all store views
        if (!in_array(static::ALL_STORE_VIEWS_ID, $this->getStoreIds())) {
            $storeIds = array_merge([static::ALL_STORE_VIEWS_ID], $this->getStoreIds());
            $this->searchCriteriaBuilder->addFilter('store_id', $storeIds, 'in');
        }

        if ($this->helper->includeDeliveryDatesReservedByUnaccomplishedOrders()) {
            // Do not include unaccomplished orders when collecting available limits
            $this->searchCriteriaBuilder->addFilter('order_address_id', null, 'notnull');
        }

        if (!$this->helper->isGeneralQueueUsed()) {
            $this->searchCriteriaBuilder->addFilter(
                'delivery_option',
                $this->getEntityId(),
                'eq'
            );
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult   = $this->queueRepository->getList($searchCriteria);

        return $searchResult;
    }

    /**
     * Returns method code for which this limit is available
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData(static::KEY_METHODS);

        if (!$methods) {
            return [];
        }

        if (!is_array($methods)) {
            $methods = explode(',', $methods);
        }

        return $methods;
    }

    /**
     * Returns array of store ids for witch this delivery option was active
     *
     * @return string[]
     */
    public function getStoreIds()
    {
        $ids = $this->getData(static::KEY_STORE_IDS);
        if (empty($ids)) {
            $ids = ['0'];
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return $ids;
    }

    /**
     * Get all available day limits (calculated from today)
     * Returns limits for each available day from today to the future, limited by the `getFutureDaysLimit()` days
     *
     * @return array
     * @throws LocalizedException
     */
    public function getDayLimits()
    {
        $data = $this->getData(static::KEY_DAY_LIMITS);
        if (empty($data)) {
            $this->fillDayLimits();
            $data = $this->getData(static::KEY_DAY_LIMITS);
        }

        return $data;
    }

    /**
     * Set start date from when delivery calculation available
     *
     * @param \DateTimeInterface|null $date
     * @return DeliveryOptionInterface
     */
    public function setStartDate(\DateTimeInterface $date = null): DeliveryOptionInterface
    {
        $this->startDate = $date;

        return $this;
    }

    /**
     * Set end date to when delivery calculation available
     *
     * @param \DateTimeInterface|null $date
     * @return DeliveryOptionInterface
     */
    public function setEndDate(\DateTimeInterface $date = null): DeliveryOptionInterface
    {
        $this->endDate = $date;

        return $this;
    }

    /**
     * Get start date from when delivery calculation available
     *
     * @return \DateTimeInterface|null
     */
    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * Get end date to when delivery calculation available
     *
     * @return \DateTimeInterface|null
     */
    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeInterface|null $date
     * @param int $daysOffset
     * @param int $maxCalculationDays
     * @return DeliveryOptionInterface
     * @throws LocalizedException
     */
    public function fillDayLimits(
        DateTimeInterface $date = null,
        $daysOffset = 0,
        $maxCalculationDays = 0
    ): DeliveryOptionInterface {
        \Magento\Framework\Profiler::start('fill_day_limits_' . $this->getId());

        $currentDayIndex               = $this->calculateStartDayIndex($date);
        $lastDayIndex                  = $this->calculateLastDayIndex($maxCalculationDays);
        $nearestDayDeliveryUnavailable = $this->isNearestDayDeliveryUnavailable();
        $firstDayFound                 = false;
        $inclNonWorkingDays            = $this->helper->includeNonWorkingDaysInProcessingOrderPeriod();
        $timeZone                      = new \DateTimeZone($this->timezone->getConfigTimezone());
        $days                          = [];
        $startDate                     = $this->getStartDate();
        $endDate                       = $this->getEndDate();

        $reservationsByDate = $this->getReservedSlotsCountByDays($startDate, $endDate);
        $precalculatedTimeLimitsByDay = $this->getAvailableTimeLimitsByDay($currentDayIndex, $lastDayIndex, $timeZone);

        while ($currentDayIndex < $lastDayIndex) {
            $futureDate = new \DateTime();
            $futureDate->setTimezone($timeZone);
            $futureDate->modify('+' . $currentDayIndex . ' day');

            if ($this->isActiveToLimitExceed($futureDate)) {
                break;
            }

            if ($startDate && !$this->isDateAfterStartDate($futureDate)) {
                // Start date is not reached yet, going to next day calculation
                $currentDayIndex++;
                $daysOffset--;
                continue;
            }

            if ($endDate && !$this->isDateBeforeEndDate($futureDate)) {
                // We reach end date and need no more calculations
                break;
            }

            if ($this->isDayHoliday($futureDate)) {
                if ($inclNonWorkingDays) {
                    $firstDayFound = true;
                    $daysOffset--;
                }
                $currentDayIndex++;
                continue;
            }

            $dayTemplate = $this->getTemplateForDate($futureDate);
            if (empty($dayTemplate['active'])) {
                if ($inclNonWorkingDays) {
                    $firstDayFound = true;
                    $daysOffset--;
                }
                $currentDayIndex++;
                continue;
            }

            if ($nearestDayDeliveryUnavailable && !$firstDayFound) {
                $firstDayFound = true;
                $currentDayIndex++;
                $daysOffset--;
                continue;
            }

            $timeLimits = $this->getTimeLimitsForDate($futureDate, false, $precalculatedTimeLimitsByDay);
            if ($timeLimits === false) {
                $currentDayIndex++;
                $daysOffset--;
                continue;
            }

            // Case when time limits is not set but quote limit by a day was exceeded
            $quotesReservedCount = (int)($reservationsByDate[$futureDate->format('Y-m-d')] ?? 0);
            if ($quotesReservedCount >= $dayTemplate['daily_quotes'] && (int)$dayTemplate['daily_quotes'] != 0) {
                $currentDayIndex++;
                $daysOffset--;
                continue;
            }

            // Skip the passed time limits
            if ($currentDayIndex == 0) {
                $originalCount = count($timeLimits);
                $currentTimeInMinutes = (int)$futureDate->format('H') * 60 + (int)$futureDate->format('i');
                foreach ($timeLimits as $limitKey => $limit) {
                    if ($limit['cut_off_time']) {
                        $cutOffTimeInMinutes = $this->helper->convertTimeStringToMinutes($limit['cut_off_time']);
                        if ($currentTimeInMinutes > $cutOffTimeInMinutes) {
                            unset($timeLimits[$limitKey]);
                        }
                    }

                    if (empty($limit['to'])) {
                        continue;
                    }

                    if ($this->helper->convertTimeStringToMinutes($limit['to']) < $currentTimeInMinutes) {
                        unset($timeLimits[$limitKey]);
                    }
                }

                if ($originalCount > 0 && empty($timeLimits)) {
                    $currentDayIndex++;
                    $daysOffset--;
                    continue;
                }
            }

            // Day offset (from product)
            if ($daysOffset > 0) {
                $currentDayIndex++;
                $daysOffset--;
                continue;
            }

            $days[$currentDayIndex]['time_limits']    = array_values($timeLimits);
            $days[$currentDayIndex]['date_formatted'] = $this->formatDateAccordingSettings(
                $currentDayIndex,
                $futureDate
            );
            $days[$currentDayIndex]['date']           = $this->formatDate($futureDate);

            if (!empty($dayTemplate['extra_charge']) && empty($timeLimits)) {
                $this->addExtraChargeDataToDay($days[$currentDayIndex], $dayTemplate['extra_charge']);
            }

            $currentDayIndex++;
        }

        $this->setDayLimits($days);
        \Magento\Framework\Profiler::stop('fill_day_limits_' . $this->getId());

        return $this;
    }

    /**
     * @param DateTimeInterface $date
     * @return string
     */
    private function formatDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    /**
     * Format date according store settings
     *
     * @param int $currentDayIndex
     * @param DateTimeInterface $futureDate
     * @return string
     */
    private function formatDateAccordingSettings(int $currentDayIndex, DateTimeInterface $futureDate): string
    {
        $replaceDateToWords = $this->helper->isNeedToReplaceDateToWords();

        if ($currentDayIndex == 0 && $replaceDateToWords) {
            return (string)__('Today');
        }

        if ($currentDayIndex == 1 && $replaceDateToWords) {
            return (string)__('Tomorrow');
        }

        return $this->helper->formatDateFromDefaultToStoreSpecific(
            $futureDate
        );
    }

    /**
     * Calculates the first day index, from which we start limits calculations
     *
     * @param DateTimeInterface|null $date
     * @return int
     */
    private function calculateStartDayIndex(DateTimeInterface $date = null): int
    {
        $startDateIncrementalIndex = $this->calculateStartDateIncrementalIndex($date);
        $startDayIndex             = $this->getStartDaysLimit() + $startDateIncrementalIndex;
        if ($date !== null && !$this->helper->includeNonWorkingDaysInProcessingOrderPeriod()) {
            $daysOfWeek      = \MageWorx\DeliveryDate\Model\Source\WorkingDays::getDaysArray();
            $workingDays     = $this->getWorkingDays();
            $currentDay      = strtolower($date->format('l'));
            $currentDayIndex = array_search($currentDay, $daysOfWeek);
            $iterations      = 0;
            while ($iterations < $startDayIndex && $startDayIndex <= $this->getFutureDaysLimit()) {
                $currentDay = $daysOfWeek[$currentDayIndex];
                if (!in_array($currentDay, $workingDays)) {
                    $startDayIndex++;
                }
                $iterations++;
                $currentDayIndex++;
                if ($currentDayIndex === 7) {
                    $currentDayIndex = 0;
                }
            }
        }

        return $startDayIndex;
    }

    /**
     * Calculates start date incremental index
     * (the day index from which we are starting calculations)
     *
     * @param DateTimeInterface|null $date
     * @return int
     */
    private function calculateStartDateIncrementalIndex(DateTimeInterface $date = null): int
    {
        $today = new \DateTime();
        if ($date === null) {
            $startDateIncrementalIndex = 0;
        } else {
            $startDateIncrementalIndexDiff = $date->diff($today, true);
            $startDateIncrementalIndex     = $startDateIncrementalIndexDiff->d;
        }

        return $startDateIncrementalIndex;
    }

    /**
     * Calculates the latest day index by which we do day limits calculation
     *
     * @param int $maxCalculationDays
     * @return int
     */
    private function calculateLastDayIndex(int $maxCalculationDays = 0): int
    {
        $lastDayIndex = $this->getFutureDaysLimit();
        if ($lastDayIndex > static::MAX_DAYS_CALCULATION) {
            $lastDayIndex = static::MAX_DAYS_CALCULATION;
        }

        if ($maxCalculationDays > 0 && $maxCalculationDays < $lastDayIndex) {
            return $maxCalculationDays;
        }

        return $lastDayIndex;
    }

    /**
     * Fill delivery option with day limits having disabled dates marked
     *
     * @param DateTimeInterface|null $date
     * @param int $daysOffset
     * @param int $maxCalculationDays
     * @return DeliveryOptionInterface
     * @throws LocalizedException
     */
    public function fillDayLimitsWithDisabled(
        DateTimeInterface $date = null,
        $daysOffset = 0,
        $maxCalculationDays = 0
    ): DeliveryOptionInterface {
        \Magento\Framework\Profiler::start('fill_day_limits_' . $this->getId());

        $startDayIndex                 = $this->calculateStartDayIndex($date);
        $lastDayIndex                  = $this->calculateLastDayIndex($maxCalculationDays);
        $nearestDayDeliveryUnavailable = $this->isNearestDayDeliveryUnavailable();
        $currentDayIndex               = 0;
        $firstDayFound                 = false;
        $timeZone                      = new \DateTimeZone($this->timezone->getConfigTimezone());
        $days                          = [];
        $startDate                     = $this->getStartDate();
        $endDate                       = $this->getEndDate();

        $reservationsByDate = $this->getReservedSlotsCountByDays($startDate, $endDate);
        $precalculatedTimeLimitsByDay = $this->getAvailableTimeLimitsByDay($currentDayIndex, $lastDayIndex, $timeZone);

        while ($currentDayIndex < $lastDayIndex) {
            $futureDate = new \DateTime();
            $futureDate->setTimezone($timeZone);
            $futureDate->modify('+' . $currentDayIndex . ' day');

            // Day outside offset
            if ($currentDayIndex < $startDayIndex) {
                $days[$currentDayIndex] = [
                    'active'         => false,
                    'available'      => 0,
                    'reserved'       => 0,
                    'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                    'date'           => $this->formatDate($futureDate),
                    'status'         => 'processing_order_period'
                ];

                $currentDayIndex++;

                continue;
            }

            // Day outside active to limitation
            if ($this->isActiveToLimitExceed($futureDate)) {
                break;
            }

            if ($startDate && !$this->isDateAfterStartDate($futureDate)) {
                // Start date is not reached yet, going to next day calculation
                $currentDayIndex++;
                $daysOffset--;
                continue;
            }

            if ($endDate && !$this->isDateBeforeEndDate($futureDate)) {
                // We reach end date and need no more calculations
                break;
            }

            // Day is a holiday
            if ($this->isDayHoliday($futureDate)) {
                $days[$currentDayIndex] = [
                    'active'         => false,
                    'available'      => 0,
                    'reserved'       => 0,
                    'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                    'date'           => $this->formatDate($futureDate),
                    'status'         => 'holiday'
                ];

                $currentDayIndex++;

                continue;
            }

            $dayTemplate = $this->getTemplateForDate($futureDate);
            // Inactive day
            if (empty($dayTemplate['active'])) {
                $days[$currentDayIndex] = [
                    'active'         => false,
                    'available'      => 0,
                    'reserved'       => 0,
                    'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                    'date'           => $this->formatDate($futureDate),
                    'status'         => 'inactive'
                ];

                $currentDayIndex++;

                continue;
            }

            // Case when time limits is not set but quote limit by a day was exceeded
            $quotesReservedCount = (int)($reservationsByDate[$futureDate->format('Y-m-d')] ?? 0);
            $timeLimits          = $this->getTimeLimitsForDate($futureDate, true, $precalculatedTimeLimitsByDay);
            if ($timeLimits === false) {
                $days[$currentDayIndex] = [
                    'active'         => false,
                    'available'      => (int)$dayTemplate['daily_quotes'],
                    'reserved'       => $quotesReservedCount,
                    'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                    'status'         => 'unavailable_by_time_limits'
                ];

                $currentDayIndex++;
                $daysOffset--;

                continue;
            }

            if ($quotesReservedCount >= $dayTemplate['daily_quotes'] && (int)$dayTemplate['daily_quotes'] != 0) {
                $days[$currentDayIndex] = [
                    'active'         => false,
                    'available'      => (int)$dayTemplate['daily_quotes'],
                    'reserved'       => $quotesReservedCount,
                    'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                    'date'           => $this->formatDate($futureDate),
                    'status'         => 'unavailable_by_day_limits'
                ];

                $currentDayIndex++;
                $daysOffset--;

                continue;
            }

            // Day offset (from product)
            if ($daysOffset > 0) {
                $days[$currentDayIndex] = [
                    'active'         => false,
                    'available'      => (int)$dayTemplate['daily_quotes'],
                    'reserved'       => $quotesReservedCount,
                    'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                    'date'           => $this->formatDate($futureDate),
                    'status'         => 'day_outside_offset'
                ];

                $currentDayIndex++;
                $daysOffset--;

                continue;
            }

            if ($nearestDayDeliveryUnavailable && !$firstDayFound) {
                $firstDayFound = true;

                $days[$currentDayIndex] = [
                    'active'         => false,
                    'available'      => (int)$dayTemplate['daily_quotes'],
                    'reserved'       => $quotesReservedCount,
                    'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                    'date'           => $this->formatDate($futureDate),
                    'status'         => 'cut_off_time_passed'
                ];

                $currentDayIndex++;
                $daysOffset--;

                continue;
            }

            // Skip the passed time limits
            if ($currentDayIndex == 0 && $daysOffset == 0) {
                $originalCount            = count($timeLimits);
                $allTimeLimitsUnavailable = true;
                foreach ($timeLimits as $limitKey => $limit) {
                    if (empty($limit['to'])) {
                        $allTimeLimitsUnavailable = false;
                        continue;
                    }

                    $currentTimeInHours = (int)$futureDate->format('H') + (int)$futureDate->format('i') / 60;
                    if ($limit['to'] < $currentTimeInHours) {
                        unset($timeLimits[$limitKey]);
                    } elseif ($limit['available'] === 0 || $limit['available'] > $limit['reserved']) {
                        $allTimeLimitsUnavailable = false;
                    }
                }

                if ($originalCount > 0 && empty($timeLimits)) {
                    $days[$currentDayIndex] = [
                        'active'         => false,
                        'available'      => (int)$dayTemplate['daily_quotes'],
                        'reserved'       => $quotesReservedCount,
                        'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                        'date'           => $this->formatDate($futureDate),
                        'status'         => 'time_limits_passed'
                    ];

                    $currentDayIndex++;
                    $daysOffset--;

                    continue;
                } elseif ($originalCount > 0 && $allTimeLimitsUnavailable) {
                    $days[$currentDayIndex] = [
                        'active'         => false,
                        'available'      => (int)$dayTemplate['daily_quotes'],
                        'reserved'       => $quotesReservedCount,
                        'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                        'date'           => $this->formatDate($futureDate),
                        'status'         => 'all_time_limits_unavailable'
                    ];

                    $currentDayIndex++;
                    $daysOffset--;

                    continue;
                } else {
                    $days[$currentDayIndex] = [
                        'active'         => true,
                        'available'      => (int)$dayTemplate['daily_quotes'],
                        'reserved'       => $quotesReservedCount,
                        'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                        'date'           => $this->formatDate($futureDate),
                        'status'         => 'available'
                    ];

                    $days[$currentDayIndex]['time_limits'] = array_values($timeLimits);
                    if (!empty($timeLimits)) {
                        $allTimeLimitsUnavailable = true;
                        foreach ($timeLimits as $timeLimit) {
                            if ($timeLimit['status'] === 'available') {
                                $allTimeLimitsUnavailable = false;
                                break;
                            }
                        }
                        if ($allTimeLimitsUnavailable) {
                            $days[$currentDayIndex]['active'] = false;
                            $days[$currentDayIndex]['status'] = 'all_time_limits_unavailable';
                        }
                    }

                    $currentDayIndex++;
                    $daysOffset--;

                    continue;
                }
            }

            $days[$currentDayIndex] = [
                'active'         => true,
                'available'      => (int)$dayTemplate['daily_quotes'],
                'reserved'       => $quotesReservedCount,
                'date_formatted' => $this->formatDateAccordingSettings($currentDayIndex, $futureDate),
                'date'           => $this->formatDate($futureDate),
                'status'         => 'available'
            ];

            $days[$currentDayIndex]['time_limits'] = array_values($timeLimits);
            if (!empty($timeLimits)) {
                $allTimeLimitsUnavailable = true;
                foreach ($timeLimits as $timeLimit) {
                    if ($timeLimit['status'] === 'available') {
                        $allTimeLimitsUnavailable = false;
                        break;
                    }
                }
                if ($allTimeLimitsUnavailable) {
                    $days[$currentDayIndex]['active'] = false;
                    $days[$currentDayIndex]['status'] = 'all_time_limits_unavailable';
                }
            }

            // Set all limits for unavailable day as unavailable
            if (isset($days[$currentDayIndex]['status']) && $days[$currentDayIndex]['status'] !== 'available') {
                foreach ($days[$currentDayIndex]['time_limits'] as $id => $timeLimit) {
                    if (isset($timeLimit['status'])) {
                        $days[$currentDayIndex]['time_limits'][$id]['status'] = 'unavailable';
                    }
                }
            }

            if (!empty($dayTemplate['extra_charge']) && empty($timeLimits)) {
                $this->addExtraChargeDataToDay($days[$currentDayIndex], $dayTemplate['extra_charge']);
            }

            $currentDayIndex++;
        }

        $this->setDayLimits($days);
        \Magento\Framework\Profiler::stop('fill_day_limits_' . $this->getId());

        return $this;
    }

    /**
     * Adds extra charge value to the day label
     *
     * @param array $currentDay
     * @param $extraChargeAmount
     */
    private function addExtraChargeDataToDay(array &$currentDay, $extraChargeAmount)
    {
        $templateData = $this->helper->getTemplateDataForDeliveryDateInput();
        if (!empty($templateData['input_type']) && $templateData['input_type'] !== 'dropdown') {
            $prefix = '<span class="data-item__price"> ';
            $suffix = '</span>';
        } else {
            $prefix = ' ';
            $suffix = '';
        }

        $amountFormatted = $this->helper->convertBaseAmountToStoreCurrencyAmount($extraChargeAmount, true);
        $amount          = $this->helper->convertBaseAmountToStoreCurrencyAmount($extraChargeAmount, false);

        $extraChargeMessage = $prefix . '+' . $amountFormatted . $suffix;

        $currentDay['date_formatted']       .= $extraChargeMessage;
        $currentDay['extra_charge']         = $amount;
        $currentDay['extra_charge_message'] = $extraChargeMessage;
    }

    /**
     * Returns first day from which limits rendered during checkout (first available day from the today)
     *
     * @return int
     */
    public function getStartDaysLimit(): int
    {
        return (int)$this->getData(static::KEY_START_DAYS_LIMITS);
    }

    /**
     * Returns count of the days for which limits rendered during checkout
     *
     * @return int
     */
    public function getFutureDaysLimit(): int
    {
        return (int)$this->getData(static::KEY_FUTURE_DAYS_LIMITS);
    }

    /**
     * Check is the Nearest Day Delivery available
     *
     * @return bool
     */
    private function isNearestDayDeliveryUnavailable(): bool
    {
        $timeZone           = new \DateTimeZone($this->timezone->getConfigTimezone());
        $storeTimeNowObject = new \DateTime('now');
        $storeTimeNowObject->setTimezone($timeZone);
        $timeNow = 60 * (int)$storeTimeNowObject->format('H') + (int)$storeTimeNowObject->format('i');

        $cutOffTimeReal = $this->getRealCutOffTime();
        if ($cutOffTimeReal) {
            return $timeNow > $cutOffTimeReal;
        } else {
            // Nearest Day Delivery time was not set
            return false;
        }
    }

    /**
     * @return int
     */
    public function getRealCutOffTime(): int
    {
        $cutOffTime = $this->getCutOffTime();
        $maxTime    = 0;

        if ($cutOffTime) {
            [$hours, $minutes] = explode(':', $cutOffTime);
            $maxTime = (int)$hours * 60 + (int)$minutes;
        }

        $actualQuote = $this->getActualQuote();
        if ($actualQuote) {
            foreach ($actualQuote->getAllItems() as $item) {
                $product = $item->getProduct();
                if (!$product instanceof \Magento\Catalog\Model\Product) {
                    continue;
                }
                $resource          = $product->getResource();
                $productCutOffTime = $resource->getAttributeRawValue(
                    $product->getId(),
                    'mw_cut_off_time',
                    $item->getStoreId()
                );

                if ($productCutOffTime) {
                    [$hours, $minutes] = explode(':', $productCutOffTime);
                    $productMaxTime = (int)$hours * 60 + (int)$minutes;
                    $maxTime        = $productMaxTime < $maxTime ? $productMaxTime : $maxTime;
                }
            }
        }

        return $maxTime;
    }

    public function getActualQuote(): ?Quote
    {
        return $this->deliveryManager->getQuote();
    }

    /**
     * Get time after which the nearest day delivery should be disabled
     *
     * @return string
     */
    public function getCutOffTime(): string
    {
        return (string)$this->getData(static::KEY_CUT_OFF_TIME);
    }

    /**
     * @param DateTimeInterface|\DateTime $day
     * @return bool
     */
    public function isActiveToLimitExceed($day): bool
    {
        // Cached holidays
        $activeTo = $this->getActiveTo();
        if (!$activeTo) {
            return false;
        }

        /** @var DateTimeInterface $holiday */
        $diff = $day->diff($activeTo);
        if ($diff && $diff->days > 0 && $diff->invert) {
            return true;
        }

        return false;
    }

    /**
     * Returns date and time to witch this delivery option was active
     *
     * @return DateTimeInterface
     * @throws \Exception
     */
    public function getActiveTo()
    {
        $day = $this->getData(static::KEY_ACTIVE_TO);
        if (!$day) {
            return null;
        }
        if (!$day instanceof DateTimeInterface) {
            $day = new \DateTime($day);
            $this->setActiveTo($day);
        }

        return $day;
    }

    /**
     * @param DateTimeInterface $to
     * @return DeliveryOptionDataInterface
     */
    public function setActiveTo(DateTimeInterface $to)
    {
        return $this->setData(static::KEY_ACTIVE_TO, $to);
    }

    /**
     * Check is selected day in a holidays list
     *
     * @param \DateTimeInterface $day
     * @return bool
     */
    public function isDayHoliday(\DateTimeInterface $day): bool
    {
        // Cached holidays
        $holidays = $this->getHolidays();
        $checkDay = clone $day;
        $checkDay->setTime(0, 0);
        /** @var DateTimeInterface $holiday */
        foreach ($holidays as $holiday) {
            $diff = $checkDay->diff($holiday, true);
            if ($diff && $diff->days === 0 && $checkDay->format('d') === $holiday->format('d')) {
                // Holiday found
                return true;
            }
        }

        return false;
    }

    /**
     * Check is a starting date passed
     *
     * @param DateTimeInterface $date
     * @return bool
     */
    public function isDateAfterStartDate(\DateTimeInterface $date): bool
    {
        $startDate = $this->getStartDate();
        if (!$startDate) {
            // When start date is not set we must calculate all days from the beginning
            return true;
        }

        $dateYmd      = (int)$date->format('Ymd');
        $startDateYmd = (int)$startDate->format('Ymd');
        $passedDay    = $dateYmd > $startDateYmd;
        $sameDay      = $dateYmd === $startDateYmd;
        if ($passedDay || $sameDay) {
            return true;
        }

        return false;
    }

    /**
     * Check is the future date less than end date of calculation specified by admin
     *
     * @param DateTimeInterface $date
     * @return bool
     */
    public function isDateBeforeEndDate(\DateTimeInterface $date): bool
    {
        $endDate = $this->getEndDate();
        if (!$endDate) {
            // When end date is not set we must calculate all days before we reach the limit
            return true;
        }

        $dateYmd     = (int)$date->format('Ymd');
        $endDateYmd  = (int)$endDate->format('Ymd');
        $isDayBefore = $endDateYmd > $dateYmd;
        $sameDay     = $dateYmd === $endDateYmd;
        if ($isDayBefore || $sameDay) {
            return true;
        }

        return false;
    }

    /**
     * Is day working
     *
     * @param DateTimeInterface $day
     * @return bool
     */
    public function isWorkingDay(DateTimeInterface $day)
    {
        $dayLong     = mb_strtolower($day->format('l'));
        $workingDays = $this->getWorkingDays();
        if (empty($workingDays)) {
            return true;
        }

        if (in_array($dayLong, $workingDays)) {
            return true;
        }

        return false;
    }

    /**
     * @return DateTimeInterface[]
     */
    public function getHolidays()
    {
        if (!empty($this->holidays) && $this->holidays) {
            return $this->holidays;
        }

        $holidaysSerialized = $this->getHolidaysSerialized();
        if (is_string($holidaysSerialized)) {
            $holidays = json_decode($holidaysSerialized, true);
        } elseif (is_array($holidaysSerialized)) {
            $holidays = $holidaysSerialized;
        } else {
            $holidays = [];
        }

        $holidaysConverted = [];
        $years             = [
            $this->getCurrentYear() - 1,
            $this->getCurrentYear(),
            $this->getCurrentYear() + 1
        ];
        foreach ($holidays as $holiday) {
            foreach ($years as $year) {
                if ($holiday['recurring']) {
                    $holiday['year'] = $year;
                }
                if (empty($holiday['month'])) {
                    $month = 1;
                    while ($month <= 12) {
                        $holiday['month']    = $month;
                        $holidaysConverted[] = $this->getDateTimeObjectForHoliday($holiday);
                        $month++;
                    }
                    $holiday['month'] = 0;
                } else {
                    $holidaysConverted[] = $this->getDateTimeObjectForHoliday($holiday);
                }
            }
        }

        $this->holidays = $holidaysConverted;

        return $this->holidays;
    }

    /**
     * @param array $value
     * @return DeliveryOptionDataInterface
     */
    public function setHolidays($value)
    {
        $this->holidays = $value;

        return $this;
    }

    /**
     * Convert data from array to the \DateTime object
     *
     * @param array $holiday
     * @return \DateTime
     */
    public function getDateTimeObjectForHoliday(array $holiday)
    {
        $day   = (int)$holiday['day'];
        $month = (int)$holiday['month'];
        if ($holiday['recurring'] && empty($holiday['year'])) {
            $year = $this->getCurrentYear();
        } elseif ($holiday['year']) {
            $year = (int)$holiday['year'];
        } else {
            $year = $this->getCurrentYear();
        }

        $dateTimeObject = new \DateTime();
        $dateTimeObject->setDate($year, $month, $day);
        $dateTimeObject->setTime(0, 0);

        return $dateTimeObject;
    }

    /**
     * Get current year 4-digit
     *
     * @return int
     * @throws \Exception
     */
    public function getCurrentYear()
    {
        $day      = new \DateTime();
        $timezone = new \DateTimeZone($this->timezone->getConfigTimezone());
        $day->setTimezone($timezone);
        $year = $day->format('Y');

        return (int)$year;
    }

    /**
     * Get concatenated order_address_ids per day
     *
     * @param int $start
     * @param int $end
     * @param \DateTimeZone $timeZone
     * @return array
     */
    public function getOrderAddressIdsByDate(
        int           $start,
        int           $end,
        \DateTimeZone $timeZone
    ): array {
        $startDay = new \DateTime();
        $startDay->setTimezone($timeZone);
        $startDay->modify('+' . $start . ' day');

        $endDay = new \DateTime();
        $endDay->setTimezone($timeZone);
        $endDay->modify('+' . $end . ' day');

        $this->searchCriteriaBuilder->addFilter('delivery_day', $startDay->format('Y-m-d'), 'gteq');
        $this->searchCriteriaBuilder->addFilter('delivery_day', $endDay->format('Y-m-d'), 'lteq');
        $this->searchCriteriaBuilder->addFilter('order_address_id', null, 'notnull');
        $this->searchCriteriaBuilder->addFilter(
            'delivery_option',
            $this->getEntityId(),
            'eq'
        );

        // Filter by a store when delivery option not for all store views
        if (!in_array(static::ALL_STORE_VIEWS_ID, $this->getStoreIds())) {
            $storeIds = array_merge([static::ALL_STORE_VIEWS_ID], $this->getStoreIds());
            $this->searchCriteriaBuilder->addFilter('store_id', $storeIds, 'in');
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orderAddressIdsByDay = $this->queueRepository->getOrderAddressIdsByDay($searchCriteria);

        return $orderAddressIdsByDay;
    }

    /**
     * @param int $start
     * @param int $end
     * @param \DateTimeZone $timeZone
     * @param $ignoreGeneralQueue
     * @return array
     */
    public function getAvailableTimeLimitsByDay(
        int           $start,
        int           $end,
        \DateTimeZone $timeZone,
        $ignoreGeneralQueue = false
    ): array {
        $startDay = new \DateTime();
        $startDay->setTimezone($timeZone);
        $startDay->modify('+' . $start . ' day');

        $endDay = new \DateTime();
        $endDay->setTimezone($timeZone);
        $endDay->modify('+' . $end . ' day');

        $this->searchCriteriaBuilder->addFilter('delivery_day', $startDay->format('Y-m-d'), 'gteq');
        $this->searchCriteriaBuilder->addFilter('delivery_day', $endDay->format('Y-m-d'), 'lteq');

        // Filter by a store when delivery option not for all store views
        if (!in_array(static::ALL_STORE_VIEWS_ID, $this->getStoreIds())) {
            $storeIds = array_merge([static::ALL_STORE_VIEWS_ID], $this->getStoreIds());
            $this->searchCriteriaBuilder->addFilter('store_id', $storeIds, 'in');
        }

        if ($this->helper->includeDeliveryDatesReservedByUnaccomplishedOrders()) {
            // Do not include unaccomplished orders when collecting available limits
            $this->searchCriteriaBuilder->addFilter('order_address_id', null, 'notnull');
        }

        if (!$this->helper->isGeneralQueueUsed() || $ignoreGeneralQueue) {
            $this->searchCriteriaBuilder->addFilter(
                'delivery_option',
                $this->getEntityId(),
                'eq'
            );
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $timeLimitsByDay = $this->queueRepository->getQueueReservedCountByDateAndTime($searchCriteria);

        return $timeLimitsByDay;
    }

    /**
     * Returns available time limits or empty when time limits is not set > array
     * Returns false in case time limits exceeded > bool
     *
     * @param \DateTime $day
     * @param bool $advanced
     * @return array|bool
     * @throws LocalizedException
     */
    private function getTimeLimitsForDate(\DateTimeInterface $day, bool $advanced, array $precalculatedTimeLimitsByDay)
    {
        $timeLimits = [];

        $dayTemplate = $this->getTemplateForDate($day);
        if (empty($dayTemplate)) {
            return $timeLimits;
        }

        if (empty($dayTemplate['active'])) {
            return $timeLimits;
        }

        if (empty($dayTemplate['time_limits'])) {
            return $timeLimits;
        }

        $dayTemplate['total_quote_reserved'] = 0;
        $timeLimitsTemplate                  = $dayTemplate['time_limits'];
        usort($timeLimitsTemplate, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }
            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        foreach ($timeLimitsTemplate as $timeLimitTemplate) {
            $status       = 'available';

            $timeParts       = $this->helper->parseFromToPartsFromTimeLimitTemplate($timeLimitTemplate);
            $timeDiapasonKey = $timeParts['from']['in_minutes'] . '_' . $timeParts['to']['in_minutes'];
            $reservedByDay   = $precalculatedTimeLimitsByDay[$day->format('Y-m-d')] ?? [];
            $reservedByTime  = $reservedByDay[$timeDiapasonKey]['reserved'] ?? 0;

            $this->addTotalQuotesToDayLimitReserve($dayTemplate, $reservedByTime);

            $unlimited = false;
            if (isset($timeLimitTemplate['quote_limit']) && $timeLimitTemplate['quote_limit'] === '') {
                $unlimited = true;
            }

            if ($this->getQuotesScope() === static::QUOTES_SCOPE_UNLIMITED) {
                $unlimited = true;
            }

            $quoteLimit = empty($timeLimitTemplate['quote_limit']) ? 0 : (int)$timeLimitTemplate['quote_limit'];

            if ($reservedByTime >= $quoteLimit && !$unlimited) {
                $status = 'unavailable';
            }

            $baseExtraCharge = empty($timeLimitTemplate['extra_charge'])
                ? 0
                : (float)$timeLimitTemplate['extra_charge'];
            if ($baseExtraCharge) {
                $extraCharge = $this->helper->convertBaseAmountToStoreCurrencyAmount($baseExtraCharge, true);
            } else {
                $extraCharge = '';
            }

            if (!$advanced && $status == 'unavailable') {
                continue;
            }

            $timeLimitData = [
                'from'         => empty($timeLimitTemplate['from'])
                    ? static::MIN_TIME_IN_DAY
                    : $timeLimitTemplate['from'],
                'to'           => empty($timeLimitTemplate['to'])
                    ? static::MAX_TIME_IN_DAY
                    : $timeLimitTemplate['to'],
                'extra_charge' => $extraCharge,
                'cut_off_time' => empty($timeLimitTemplate['cut_off_time'])
                    ? null
                    : $timeLimitTemplate['cut_off_time'],
            ];

            if ($advanced) {
                $timeLimitData['available'] = $quoteLimit;
                $timeLimitData['reserved']  = $reservedByTime;
                $timeLimitData['status']    = $status;
            }

            $timeLimits[] = $timeLimitData;

            // Check an overall day limits each time and return nothing in case an overall limits exceed
            if ($dayTemplate['daily_quotes']
                && $dayTemplate['total_quote_reserved'] >= $dayTemplate['daily_quotes']
                && !$advanced
            ) {
                return [];
            }
        }

        // Quote limits exceed by time limits quote but quote limit by day was not exceed: day should be disabled
        if (!empty($timeLimitsTemplate) && empty($timeLimits) && !$advanced) {
            return false;
        }

        return $timeLimits;
    }

    /**
     * Increase limits reserve in the day
     *
     * @param array $dayTemplate
     * @param int $totalCount
     * @return
     */
    private function addTotalQuotesToDayLimitReserve(array &$dayTemplate, $totalCount = 0)
    {
        if (empty($dayTemplate['total_quote_reserved'])) {
            $dayTemplate['total_quote_reserved'] = 0;
        }
        $dayTemplate['total_quote_reserved'] += $totalCount;

        return $dayTemplate;
    }

    /**
     * @param DateTimeInterface|null $from
     * @param DateTimeInterface|null $to
     * @param bool|null $ignoreGeneralQueue
     * @return array
     */
    public function getReservedSlotsCountByDays(
        ?DateTimeInterface $from,
        ?DateTimeInterface $to,
        $ignoreGeneralQueue = false
    ): array {
        $from = $from ?? $this->getStartDate();
        $to   = $to ?? $this->getStartDate();

        // From date to date filter
        if ($from !== null) {
            $this->searchCriteriaBuilder->addFilter('delivery_day', $from->format('Y-m-d'), 'gteq');
        }
        if ($to !== null) {
            $this->searchCriteriaBuilder->addFilter('delivery_day', $to->format('Y-m-d'), 'lteq');
        }

        // Filter by a store when delivery option not for all store views
        if (!in_array(static::ALL_STORE_VIEWS_ID, $this->getStoreIds())) {
            $storeIds = array_merge([static::ALL_STORE_VIEWS_ID], $this->getStoreIds());
            $this->searchCriteriaBuilder->addFilter('store_id', $storeIds, 'in');
        }

        if ($this->helper->includeDeliveryDatesReservedByUnaccomplishedOrders()) {
            // Do not include unaccomplished orders when collecting available limits
            $this->searchCriteriaBuilder->addFilter('order_address_id', null, 'notnull');
        }

        if (!$this->helper->isGeneralQueueUsed() || $ignoreGeneralQueue) {
            $this->searchCriteriaBuilder->addFilter(
                'delivery_option',
                $this->getEntityId(),
                'eq'
            );
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $reservationsByDate = $this->queueRepository->getQueueReservedCountByDays($searchCriteria);

        return $reservationsByDate;
    }

    /**
     * Get count of already reserved quotes for specified day
     *
     * @param DateTimeInterface $day
     * @param bool $ignoreGeneralQueue
     * @return SearchResultsInterface
     */
    public function getReservedQuotesForDay(DateTimeInterface $day, $ignoreGeneralQueue = false): SearchResultsInterface
    {
        $this->searchCriteriaBuilder->addFilter('delivery_day', $day->format('Y-m-d'), 'eq');

        // Filter by a store when delivery option not for all store views
        if (!in_array(static::ALL_STORE_VIEWS_ID, $this->getStoreIds())) {
            $storeIds = array_merge([static::ALL_STORE_VIEWS_ID], $this->getStoreIds());
            $this->searchCriteriaBuilder->addFilter('store_id', $storeIds, 'in');
        }

        if ($this->helper->includeDeliveryDatesReservedByUnaccomplishedOrders()) {
            // Do not include unaccomplished orders when collecting available limits
            $this->searchCriteriaBuilder->addFilter('order_address_id', null, 'notnull');
        }

        if (!$this->helper->isGeneralQueueUsed() || $ignoreGeneralQueue) {
            $this->searchCriteriaBuilder->addFilter(
                'delivery_option',
                $this->getEntityId(),
                'eq'
            );
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult   = $this->queueRepository->getList($searchCriteria);

        return $searchResult;
    }

    /**
     * Set day limits calculated
     *
     * @param array $array
     * @return DeliveryOptionInterface
     */
    public function setDayLimits(array $array)
    {
        return $this->setData(static::KEY_DAY_LIMITS, $array);
    }

    /**
     * Validate model data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[] - return true if validation passed successfully. Array with errors description otherwise
     * @throws \Exception
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result   = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasActiveFrom() && $dataObject->hasActiveTo()) {
            $fromDate = $dataObject->getActiveFrom();
            $toDate   = $dataObject->getActiveTo();
        }

        if ($fromDate && $toDate) {
            $fromDate = $this->timezone->date($fromDate);
            $toDate   = $this->timezone->date($toDate);

            if ($fromDate->getTimestamp() > $toDate->getTimestamp()) {
                $result[] = __('End Date should not be less than Start Date.');
            }
        }

        return !empty($result) ? $result : true;
    }

    /**
     * @return int
     * @see \MageWorx\DeliveryDate\Model\Source\ShippingMethodsChoiceLimiter
     */
    public function getShippingMethodsChoiceLimiter(): int
    {
        return (int)$this->getData(static::KEY_SHIPPING_METHODS_CHOICE_LIMITER);
    }

    /**
     * @param int $value
     * @return DeliveryOptionDataInterface
     */
    public function setShippingMethodsChoiceLimiter(int $value): DeliveryOptionDataInterface
    {
        return $this->setData(static::KEY_SHIPPING_METHODS_CHOICE_LIMITER, $value);
    }

    /**
     * Set working days for which delivery option is available
     *
     * @param array|string|null $value
     * @return DeliveryOptionDataInterface
     */
    public function setWorkingDays($value)
    {
        return $this->setData(static::KEY_WORKING_DAYS, $value);
    }

    /**
     * Set time after which the nearest day delivery should be disabled
     *
     * @param string|null $value
     * @return DeliveryOptionDataInterface
     */
    public function setCutOffTime($value)
    {
        return $this->setData(static::KEY_CUT_OFF_TIME, $value);
    }

    /**
     * Set quotes scope
     *
     * @param int $value
     * @return DeliveryOptionDataInterface
     * @see \MageWorx\DeliveryDate\Model\Source\QuotesScope
     *
     */
    public function setQuotesScope($value)
    {
        return $this->setData(static::KEY_QUOTES_SCOPE, $value);
    }

    /**
     * Set weekdays for which delivery option is unavailable
     *
     * @param array $nonWorkingDays
     */
    public function setNonWorkingDaysByCart(array $nonWorkingDays = [])
    {
        $this->nonWorkingDays = $nonWorkingDays;
    }

    /**
     * Get weekdays for which delivery option is unavailable
     *
     * @return array
     */
    public function getNonWorkingDaysByCart(): array
    {
        return $this->nonWorkingDays;
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption::class);
        $this->setIdFieldName(static::KEY_ID);
    }

    /**
     * Set error message for case: delivery date is required
     *
     * @param string[] $messages
     * @return DeliveryOptionDataInterface
     */
    public function setDeliveryDateRequiredErrorMessages(array $messages): DeliveryOptionDataInterface
    {
        $this->setData(static::KEY_DELIVERY_DATE_REQUIRED_ERROR_MESSAGE, $messages);

        return $this;
    }

    /**
     * Get error messages for case: delivery date is required (per store)
     *
     * @return array|null
     */
    public function getDeliveryDateRequiredErrorMessages(): ?array
    {
        return $this->getData(static::KEY_DELIVERY_DATE_REQUIRED_ERROR_MESSAGE);
    }

    /**
     * Get error messages for case: delivery date is required.
     * For specified or actual store.
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getDeliveryDateRequiredErrorMessage(int $storeId = null): ?string
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $messages = $this->getDeliveryDateRequiredErrorMessages() ?? [];

        return $messages[$storeId] ?? null;
    }

    /**
     * Blocks selection of delivery date on the checkout for the customers
     *
     * @param bool $flag
     * @return DeliveryOptionDataInterface
     */
    public function setDisableSelection(bool $flag): DeliveryOptionDataInterface
    {
        $this->setData(static::KEY_DISABLE_DELIVERY_DATE_SELECTION, $flag);

        return $this;
    }

    /**
     * Blocks selection of delivery date on the checkout for the customers
     *
     * @return bool
     */
    public function getDisableSelection(): bool
    {
        return (bool)$this->getData(static::KEY_DISABLE_DELIVERY_DATE_SELECTION);
    }

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setConditionsSerialized(string $value): DeliveryOptionDataInterface
    {
        return $this->setData(static::KEY_CONDITIONS_SERIALIZED, $value);
    }

    /**
     * @return array
     */
    public function getConditionsSerialized(): array
    {
        $conditions = $this->getData(static::KEY_CONDITIONS_SERIALIZED);
        if (empty($conditions)) {
            $conditions = [];
        }

        if (is_string($conditions)) {
            $conditions = json_decode($conditions, true);
        }

        if (!is_array($conditions)) {
            $conditions = [];
        }

        return $conditions;
    }

    /**
     * Check is the Delivery Option applicable for the Shipping Method
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isApplicableForShippingMethod(string $shippingMethod): bool
    {
        if ($this->getShippingMethodsChoiceLimiter() === DeliveryOptionInterface::SHIPPING_METHODS_CHOICE_LIMIT_ALL_METHODS) {
            return true;
        }

        $availableMethods = $this->getMethods();

        return in_array($shippingMethod, $availableMethods);
    }
}
