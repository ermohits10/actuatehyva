<?php
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\Validator\RestrictionMode;

use DateInterval;
use DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Exceptions\DeliveryTimeException;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use MageWorx\DeliveryDate\Model\DeliveryOption;

class QuoteAddressValidator implements \MageWorx\DeliveryDate\Api\QuoteAddressValidatorInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var QueueManagerInterface
     */
    private $queueManager;

    /**
     * @var DeliveryManagerInterface
     */
    private $deliveryManager;

    /**
     * ValidateDeliveryDateAvailability constructor.
     *
     * @param Helper $helper
     * @param TimezoneInterface $timezone
     * @param QueueManagerInterface $queueManager
     * @param DeliveryManagerInterface $deliveryManager
     */
    public function __construct(
        Helper $helper,
        TimezoneInterface $timezone,
        QueueManagerInterface $queueManager,
        DeliveryManagerInterface $deliveryManager
    ) {
        $this->helper          = $helper;
        $this->timezone        = $timezone;
        $this->queueManager    = $queueManager;
        $this->deliveryManager = $deliveryManager;
    }

    /**
     * @inheritDoc
     */
    public function validate(
        \Magento\Quote\Api\Data\CartInterface $quote,
        \Magento\Quote\Api\Data\AddressInterface $address
    ): void {
        $deliveryDay         = $address->getExtensionAttributes()->getDeliveryDay();
        $deliveryHoursFrom   = (int)$address->getExtensionAttributes()->getDeliveryHoursFrom();
        $deliveryMinutesFrom = (int)$address->getExtensionAttributes()->getDeliveryMinutesFrom();
        $deliveryHoursTo     = (int)$address->getExtensionAttributes()->getDeliveryHoursTo();
        $deliveryMinutesTo   = (int)$address->getExtensionAttributes()->getDeliveryMinutesTo();

        $currentDayTime = $this->getCurrentDateTime();

        $shippingMethod = $address->getShippingMethod();
        if (!$shippingMethod) {
            return;
        }

        // Temp: skip in-store pickup method
        if ($shippingMethod === 'instore_pickup') {
            return;
        }

        if ($this->deliveryManager->deliveryTimeDisabledOnAllProducts($quote)) {
            return;
        }

        try {
            $deliveryOption = $this->findDeliveryOption($quote, $shippingMethod, $currentDayTime);
        } catch (DeliveryTimeException $deliveryTimeException) {
            if ($this->helper->isDeliveryDateRequired($quote->getStoreId())) {
                throw new LocalizedException(
                    __('Delivery date is required. Please select delivery date and proceed.')
                );
            } else {
                return;
            }
        }

        if (!$deliveryDay) {
            if ($deliveryOption && $deliveryOption->getId()) {
                if ($this->helper->isDeliveryDateRequired()) {
                    $error = $deliveryOption->getDeliveryDateRequiredErrorMessage() ?? __(
                            'Delivery date is required. Please select delivery date and proceed.'
                        );
                    throw new LocalizedException(
                        __($error)
                    );
                } else {
                    return;
                }
            } else {
                // Delivery date is completely unavailable for this method so proeed without error
                return;
            }
        }

        $day  = $this->getSelectedDay($deliveryDay, $deliveryHoursTo, $deliveryMinutesTo);
        $diff = $currentDayTime->diff($day);

        $this->validatePassDate($diff);

        $this->validateDayLimits($deliveryOption, $diff);
        $this->validateByCutOffTime($deliveryOption, $currentDayTime, $day);
        $this->validateHoliday($deliveryOption, $day);
        $this->validateLimitsExceeded(
            $deliveryOption,
            $day,
            $quote,
            $deliveryHoursFrom,
            $deliveryHoursTo,
            $deliveryMinutesFrom,
            $deliveryMinutesTo
        );
        // All ok
    }

    /**
     * @param string|null $deliveryDay
     * @param int|null $deliveryHoursTo
     * @param int|null $deliveryMinutesTo
     * @return bool|DateTime
     */
    private function getSelectedDay(
        $deliveryDay,
        int $deliveryHoursTo = null,
        int $deliveryMinutesTo = null
    ): \DateTimeInterface {
        $day = DateTime::createFromFormat(
            'Y-m-d',
            $deliveryDay,
            new \DateTimeZone($this->timezone->getConfigTimezone())
        );

        if ($deliveryHoursTo || $deliveryMinutesTo) {
            $day->setTime($deliveryHoursTo, $deliveryMinutesTo);
        }

        return $day;
    }

    /**
     * @return DateTime
     * @throws \Exception
     */
    private function getCurrentDateTime(): \DateTimeInterface
    {
        $currentDayTime = new DateTime();
        $storeTimeZone  = new \DateTimeZone($this->timezone->getConfigTimezone());
        $currentDayTime->setTimezone($storeTimeZone);

        return $currentDayTime;
    }

    /**
     * @param DateInterval $diff
     * @throws LocalizedException
     */
    private function validatePassDate(DateInterval $diff): void
    {
        if ($diff->invert && ($diff->days > 0 || ($diff->h * 60 + $diff->i) > 0)) {
            throw new LocalizedException(
                __(
                    'Selected Delivery Date is not available right now.
                        Please select another Delivery Date and try again.'
                )
            );
        }
    }

    /**
     * @param DeliveryOption $deliveryOption
     * @param DateInterval $diff
     * @throws LocalizedException
     */
    private function validateDayLimits(DeliveryOption $deliveryOption, DateInterval $diff): void
    {
        $dayLimits = $deliveryOption->getDayLimits();
        if (empty($dayLimits)) {
            throw new LocalizedException(
                __(
                    'Selected Delivery Date is not available right now.
                        Please select another Delivery Date and try again.'
                )
            );
        }

        $dayLimitIndexes        = array_keys($dayLimits);
        $firstAvailableDayIndex = reset($dayLimitIndexes);
        if (!$diff->invert && ($diff->days + 1) < $firstAvailableDayIndex) {
            throw new LocalizedException(
                __(
                    'Selected Delivery Date is not available right now.
                        Please select another Delivery Date and try again.'
                )
            );
        }
    }

    /**
     * @param DeliveryOption $deliveryOption
     * @param DateTime $currentDayTime
     * @param DateTime $day
     * @throws LocalizedException
     */
    private function validateByCutOffTime(DeliveryOption $deliveryOption, DateTime $currentDayTime, DateTime $day): void
    {
        if ($deliveryOption->getCutOffTime()) {
            if ((int)$currentDayTime->format('d') === (int)$day->format('d')) {
                $cutOffTime                = (string)$deliveryOption->getCutOffTime();
                $currentHours              = (int)$currentDayTime->format('H');
                $currentMinutes            = (int)$currentDayTime->format('i');
                $minutesFromMidnight       = $currentHours * 60 + $currentMinutes;
                $cutOffTimeParts           = explode(':', $cutOffTime);
                $cutOffMinutesFromMidnight = (int)$cutOffTimeParts[0] * 60
                    + (int)$cutOffTimeParts[1];

                if ($minutesFromMidnight > $cutOffMinutesFromMidnight) {
                    throw new LocalizedException(
                        __('Same day delivery is not available after %1', $cutOffTime)
                    );
                }
            }
        }
    }

    /**
     * @param DeliveryOption $deliveryOption
     * @param DateTime $day
     * @throws LocalizedException
     */
    private function validateHoliday(DeliveryOption $deliveryOption, DateTime $day): void
    {
        $holiday = $deliveryOption->isDayHoliday($day);
        if ($holiday) {
            throw new LocalizedException(
                __(
                    'Selected Delivery Date is a holiday.
                        Please select another Delivery Date and try again.'
                )
            );
        }
    }

    /**
     * @param DeliveryOption $deliveryOption
     * @param DateTime $day
     * @param Quote $quote
     * @param int|null $deliveryHoursFrom
     * @param int|null $deliveryHoursTo
     * @param int|null $deliveryMinutesFrom
     * @param int|null $deliveryMinutesTo
     * @throws LocalizedException
     */
    private function validateLimitsExceeded(
        DeliveryOption $deliveryOption,
        DateTime $day,
        Quote $quote,
        int $deliveryHoursFrom = null,
        int $deliveryHoursTo = null,
        int $deliveryMinutesFrom = null,
        int $deliveryMinutesTo = null
    ): void {
        $limitExceeded = $this->queueManager->isLimitExceeded(
            $deliveryOption,
            $day,
            $deliveryHoursFrom,
            $deliveryHoursTo,
            $deliveryMinutesFrom,
            $deliveryMinutesTo
        );

        if ($limitExceeded) {
            $deliveryDateFormatted = $this->helper->formatDateFromDefaultToStoreSpecific(
                $day,
                $quote->getStoreId()
            );
            if ($deliveryHoursFrom && $deliveryMinutesFrom) {
                $deliveryDateFormatted .= ' ' . $deliveryHoursFrom . ':' . $deliveryMinutesFrom . ' - ' .
                    $deliveryHoursTo . ':' . $deliveryMinutesTo;
            }
            throw new LocalizedException(
                __(
                    'Selected Delivery Date (%1) is not available right now.
                        Please select another Delivery Date and try again.',
                    $deliveryDateFormatted
                )
            );
        }
    }

    /**
     * @param Quote $quote
     * @param string $shippingMethod
     * @param DateTime $currentDayTime
     * @return DeliveryOption
     * @throws \Exception
     */
    private function findDeliveryOption(Quote $quote, string $shippingMethod, DateTime $currentDayTime): DeliveryOption
    {
        $this->deliveryManager->setDaysOffset($this->deliveryManager->calculateDaysOffset($quote));
        $customerGroupId = (int)$quote->getCustomer()->getGroupId();
        $storeId         = (int)$quote->getStoreId();
        $this->deliveryManager->setQuote($quote);
        /** @var DeliveryOption $deliveryOption */
        $deliveryOption = $this->deliveryManager->getDeliveryOptionForMethod(
            $shippingMethod,
            $currentDayTime,
            $customerGroupId,
            $storeId
        );

        return $deliveryOption;
    }
}
