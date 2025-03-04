<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml\Queue\Calendar;

use DateTimeZone;
use Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;
use MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use MageWorx\DeliveryDate\Helper\Data as Helper;

class Config extends Template
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var DeliveryOptionRepositoryInterface
     */
    private $deliveryOptionRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var array
     */
    private $orderAddressIdsByDay = [];

    /**
     * @var array
     */
    private $reservationsByDate = [];

    /**
     * @var array
     */
    private $timeLimitsReservedByDate = [];

    /**
     * Config constructor.
     *
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param DeliveryOptionRepositoryInterface $deliveryOptionRepository
     * @param Template\Context $context
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        SearchCriteriaBuilderFactory      $searchCriteriaBuilderFactory,
        DeliveryOptionRepositoryInterface $deliveryOptionRepository,
        Helper                            $helper,
        Template\Context                  $context,
        TimezoneInterface                 $timezone,
        array                             $data = []
    ) {
        parent::__construct($context, $data);
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->deliveryOptionRepository     = $deliveryOptionRepository;
        $this->timezone                     = $timezone;
        $this->helper                       = $helper;
    }

    /**
     * @return string
     */
    public function getStoreTimezone(): string
    {
        return $this->timezone->getConfigTimezone();
    }

    protected function prepareData(
        int                     $startDayIndex,
        int                     $endDayIndex,
        DateTimeZone            $timeZone,
        DeliveryOptionInterface $deliveryOption
    ) {
        $startDate = $this->getDateUsingIndex($startDayIndex, $timeZone);
        $endDate   = $this->getDateUsingIndex($endDayIndex, $timeZone);

        $this->reservationsByDate       = $deliveryOption->getReservedSlotsCountByDays(
            $startDate,
            $endDate,
            true
        );
        $this->orderAddressIdsByDay     = $deliveryOption->getOrderAddressIdsByDate(
            $startDayIndex,
            $endDayIndex,
            $timeZone
        );
        $this->timeLimitsReservedByDate = $deliveryOption->getAvailableTimeLimitsByDay(
            $startDayIndex,
            $endDayIndex,
            $timeZone,
            true
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getConfig()
    {
        $deliveryOptionId = $this->_request->getParam('id');
        if (!$deliveryOptionId) {
            return [];
        }

        try {
            $deliveryOption = $this->deliveryOptionRepository->getById($deliveryOptionId);
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getLogMessage());

            return [];
        }

        $data = [
            'delivery_option' => [
                'id' => $deliveryOption->getId()
            ]
        ];

        $startDayIndex   = Helper::MIN_DAYS_RANGE;
        $endDayIndex     = Helper::MAX_DAYS_RANGE;
        $currentDayIndex = $startDayIndex ? $startDayIndex : 0;
        $daysData        = [];
        $timeZone        = new DateTimeZone($this->timezone->getConfigTimezone());

        // Prepare data about reserved dates, time, order ids etc. Used to reduce number of queries.
        $this->prepareData($startDayIndex, $endDayIndex, $timeZone, $deliveryOption);

        while ($currentDayIndex < $endDayIndex) {
            // Create date
            $date         = $this->getDateUsingIndex($currentDayIndex, $timeZone);
            $limit        = $deliveryOption->getLimit($date, true);
            $isHoliday    = $deliveryOption->isDayHoliday($date);
            $isWorkingDay = $deliveryOption->isWorkingDay($date);

            // Calculate available
            $available = $this->calculateAvailable($limit);

            // Calculate reserved
            $reserved = $this->reservationsByDate[$date->format('Y-m-d')] ?? 0;

            // Collect order ids
            $dayOrderIdsConcatenated = $this->collectOrderIds($date);

            // Collect limits
            $limits = $this->collectTimeLimits($limit, $deliveryOption, $date);

            $daysData[] = [
                'date'              => $date->format('Y-m-d'),
                'index'             => $currentDayIndex,
                'available'         => (int)$available,
                'reserved'          => (int)$reserved,
                'limits'            => $limits,
                'order_address_ids' => $dayOrderIdsConcatenated,
                'holiday'           => $isHoliday,
                'non_working_day'   => !$isWorkingDay
            ];

            $currentDayIndex++;
        }

        $data['delivery_option']['days'] = $daysData;

        return $data;
    }

    /**
     * @return \MageWorx\DeliveryDate\Api\Data\DeliveryOptionDataInterface[]|\Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getAllDeliveryOptions()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria        = $searchCriteriaBuilder->create();
        $deliveryOptionsList   = $this->deliveryOptionRepository->getList($searchCriteria, true);

        return $deliveryOptionsList->getItems();
    }

    /**
     * Collect time limits for specified date
     *
     * @param array $limit
     * @param DeliveryOptionInterface $deliveryOption
     * @param \DateTimeInterface $date
     * @return array
     */
    private function collectTimeLimits(array $limit, DeliveryOptionInterface $deliveryOption, \DateTimeInterface $date)
    {
        $limits = [];
        if (!empty($limit['time_limits'])) {
            $limitsReserved = $this->timeLimitsReservedByDate[$date->format('Y-m-d')] ?? [];
            foreach ($limit['time_limits'] as $timeLimit) {
                $timeLimitKey          = $timeLimit['from'] . '_' . $timeLimit['to'];
                $timeLimitAvailable    = $timeLimit['quote_limit'];
                $timeParts             = $this->helper->parseFromToPartsFromTimeLimitTemplate($timeLimit);
                $timeLimitInMinutesKey = $timeParts['from']['in_minutes'] . '_' . $timeParts['to']['in_minutes'];

                $timeLimitReserved = $limitsReserved[$timeLimitInMinutesKey]['reserved'] ?? 0;
                if (!$timeLimitReserved) {
                    continue;
                }
                $orderAddressIdsConcatenated = $limitsReserved[$timeLimitInMinutesKey]['order_address_ids'] ?? '';
                $limits[$timeLimitKey]       = [
                    'available'         => $timeLimitAvailable,
                    'reserved'          => $timeLimitReserved,
                    'order_address_ids' => $orderAddressIdsConcatenated
                ];
            }
        }

        return $limits;
    }

    /**
     * Calculate how much quotes available
     *
     * @param array $limit
     * @return int
     */
    private function calculateAvailable(array $limit): int
    {
        if (isset($limit['daily_quotes'])) {
            $available = $limit['daily_quotes'] === DeliveryOptionInterface::QUOTES_UNLIMITED ?
                0 :
                (int)$limit['daily_quotes'];
        } else {
            $available = 0;
        }

        return $available;
    }

    /**
     * Collect available orders ids.
     * Implodes them using ","
     *
     * @param \Magento\Framework\Api\SearchResultsInterface $searchResult
     * @return string
     */
    private function collectOrderIds(\DateTimeInterface $date)
    {
        return $this->orderAddressIdsByDay[$date->format('Y-m-d')] ?? '';
    }

    /**
     * @param int $index
     * @param DateTimeZone $timeZone
     * @return \DateTimeInterface
     * @throws \Exception
     */
    private function getDateUsingIndex(int $index, DateTimeZone $timeZone): \DateTimeInterface
    {
        $dayDiffSigned = ($index >= 0 ? "+" : "") . $index . " days";
        $date          = new \DateTime(date('Y-m-d', strtotime($dayDiffSigned)), $timeZone);
        $date->setTime(12, 0);

        return $date;
    }
}
