<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\Utils;

use MageWorx\DeliveryDate\Api\Data\DayLimitInterface;
use MageWorx\DeliveryDate\Api\Data\LimitsInterface;
use MageWorx\DeliveryDate\Api\Data\TimeLimitInterface;
use MageWorx\DeliveryDate\Api\DeliveryLimitsConverterInterface;

/**
 * Convert day\time limits from general array to array of objects.
 */
class DeliveryLimitsConverter implements DeliveryLimitsConverterInterface
{
    /**
     * @var \MageWorx\DeliveryDate\Api\Data\LimitsInterfaceFactory
     */
    protected $limitsFactory;

    /**
     * @var \MageWorx\DeliveryDate\Api\Data\DayLimitInterfaceFactory
     */
    protected $dayLimitFactory;

    /**
     * @var \MageWorx\DeliveryDate\Api\Data\TimeLimitInterfaceFactory
     */
    protected $timeLimitFactory;

    /**
     * @param \MageWorx\DeliveryDate\Api\Data\LimitsInterfaceFactory $limitsFactory
     * @param \MageWorx\DeliveryDate\Api\Data\DayLimitInterfaceFactory $dayLimitFactory
     * @param \MageWorx\DeliveryDate\Api\Data\TimeLimitInterfaceFactory $timeLimitFactory
     */
    public function __construct(
        \MageWorx\DeliveryDate\Api\Data\LimitsInterfaceFactory    $limitsFactory,
        \MageWorx\DeliveryDate\Api\Data\DayLimitInterfaceFactory  $dayLimitFactory,
        \MageWorx\DeliveryDate\Api\Data\TimeLimitInterfaceFactory $timeLimitFactory
    ) {
        $this->limitsFactory    = $limitsFactory;
        $this->dayLimitFactory  = $dayLimitFactory;
        $this->timeLimitFactory = $timeLimitFactory;
    }

    /**
     * @inheritDoc
     */
    public function convertToObjectArray(array $limitsArray): array
    {
        $result = [];

        foreach ($limitsArray as $method => $limit) {
            /** @var LimitsInterface $limitsObject */
            $limitsObject = $this->limitsFactory->create();
            $limitsObject->setMethod($method);
            $limitsObject->setEntityId(isset($limit['entity_id']) ? (int)$limit['entity_id'] : null);
            $limitsObject->setName($limit['name'] ?? null);
            $limitsObject->setMethods($limit['methods'] ?? null);
            $limitsObject->setIsActive(isset($limit['is_active']) ? (bool)$limit['is_active'] : false);
            $limitsObject->setSortOrder(isset($limit['sort_order']) ? (int)$limit['sort_order'] : null);
            $limitsObject->setFutureDaysLimit(
                isset($limit['future_days_limit']) ? (int)$limit['future_days_limit'] : null
            );
            $limitsObject->setStartDaysLimit(
                isset($limit['start_days_limit']) ? (int)$limit['start_days_limit'] : null
            );

            $limitsObject->setActiveFrom(isset($limit['active_from']) ? $this->dateToString($limit['active_from']) : null);
            $limitsObject->setActiveTo(isset($limit['active_to']) ? $this->dateToString($limit['active_to']) : null);
            $limitsObject->setShippingMethodsChoiceLimiter(
                isset($limit['shipping_methods_choice_limiter']) ? (int)$limit['shipping_methods_choice_limiter'] : null
            );
            $limitsObject->setWorkingDays($limit['working_days'] ?? null);
            $limitsObject->setCutOffTime($limit['cut_off_time'] ?? null);
            $limitsObject->setQuotesScope(isset($limit['quotes_scope']) ? (int)$limit['quotes_scope'] : null);
            $limitsObject->setStoreIds($limit['store_ids'] ?? []);
            $limitsObject->setCustomerGroupIds($limit['customer_group_ids'] ?? []);

            $dayLimitsObjects = [];
            if (!empty($limit['day_limits'])) {
                foreach ($limit['day_limits'] as $dayIndex => $dayLimit) {
                    /** @var DayLimitInterface $dayLimitObject */
                    $dayLimitObject = $this->dayLimitFactory->create();
                    $dayLimitObject->setDayIndex((int)$dayIndex);
                    $dayLimitObject->setDateFormatted($dayLimit['date_formatted']);
                    $dayLimitObject->setDate((string)$dayLimit['date']);

                    $timeLimitsObjects = [];
                    if (!empty($dayLimit['time_limits'])) {
                        foreach ($dayLimit['time_limits'] as $timeLimit) {
                            /** @var TimeLimitInterface $timeLimitObject */
                            $timeLimitObject = $this->timeLimitFactory->create();
                            $timeLimitObject->setFrom($timeLimit['from']);
                            $timeLimitObject->setTo($timeLimit['to']);
                            $timeLimitObject->setExtraCharge($timeLimit['extra_charge'] ?? null);

                            $timeLimitsObjects[] = $timeLimitObject;
                        }
                    } else {
                        $dayLimitObject->setExtraCharge(
                            isset($dayLimit['extra_charge']) ? (float)$dayLimit['extra_charge'] : 0
                        );
                        $dayLimitObject->setExtraChargeMessage(
                            isset($dayLimit['extra_charge_message']) ? (string)$dayLimit['extra_charge_message'] : ''
                        );
                    }

                    $dayLimitObject->setTimeLimits($timeLimitsObjects);

                    $dayLimitsObjects[] = $dayLimitObject;
                }
            }

            $limitsObject->setDayLimits($dayLimitsObjects);

            $result[] = $limitsObject;
        }

        return $result;
    }

    protected function dateToString($dateTime): string
    {
        if ($dateTime instanceof \DateTimeInterface) {
            return $dateTime->format('Y-m-d');
        }

        return (string)$dateTime;
    }
}
