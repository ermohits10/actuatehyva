<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;

class ValidatorHelper
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public function getCurrentDateTime(): \DateTimeInterface
    {
        $currentDayTime = new \DateTime();
        $storeTimeZone  = new \DateTimeZone($this->timezone->getConfigTimezone());
        $currentDayTime->setTimezone($storeTimeZone);

        return $currentDayTime;
    }

    /**
     * @param DeliveryDateDataInterface $deliveryDateData
     * @return \DateTimeInterface
     * @throws LocalizedException
     */
    public function getSelectedDay(DeliveryDateDataInterface $deliveryDateData): \DateTimeInterface
    {
        $deliveryDay = $deliveryDateData->getDeliveryDay();
        if (!$deliveryDay) {
            throw new LocalizedException(__('Delivery Date is not set. Unable to convert empty value to DateTime object.'));
        }

        $day = \DateTime::createFromFormat(
            'Y-m-d',
            $deliveryDay,
            new \DateTimeZone($this->timezone->getConfigTimezone())
        );

        if ($day === false) {
            throw new LocalizedException(__('Unable to convert (%1) value to DateTime object.', $deliveryDay));
        }

        $deliveryHoursTo = $deliveryDateData->getDeliveryHoursTo();
        $deliveryMinutesTo = $deliveryDateData->getDeliveryMinutesTo();

        if ($deliveryHoursTo || $deliveryMinutesTo) {
            $day->setTime($deliveryHoursTo, $deliveryMinutesTo);
        }

        return $day;
    }
}
