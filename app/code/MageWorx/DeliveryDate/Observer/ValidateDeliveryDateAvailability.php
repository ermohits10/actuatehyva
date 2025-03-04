<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer;

use DateInterval;
use DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterfaceFactory;
use MageWorx\DeliveryDate\Api\DeliveryDateValidatorPoolInterface;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Exceptions\DeliveryTimeException;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use MageWorx\DeliveryDate\Model\DeliveryOption;

class ValidateDeliveryDateAvailability implements ObserverInterface
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
     * @var DeliveryDateValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var DeliveryDateDataInterfaceFactory
     */
    private $deliveryDateDataFactory;

    /**
     * ValidateDeliveryDateAvailability constructor.
     *
     * @param Helper $helper
     * @param TimezoneInterface $timezone
     * @param QueueManagerInterface $queueManager
     * @param DeliveryManagerInterface $deliveryManager
     */
    public function __construct(
        Helper                             $helper,
        TimezoneInterface                  $timezone,
        QueueManagerInterface              $queueManager,
        DeliveryManagerInterface           $deliveryManager,
        DeliveryDateValidatorPoolInterface $validatorPool,
        DeliveryDateDataInterfaceFactory   $deliveryDateDataFactory
    ) {
        $this->helper                  = $helper;
        $this->timezone                = $timezone;
        $this->queueManager            = $queueManager;
        $this->deliveryManager         = $deliveryManager;
        $this->validatorPool           = $validatorPool;
        $this->deliveryDateDataFactory = $deliveryDateDataFactory;
    }

    /**
     * Validate delivery date when client try to place order
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        if (!$quote || !$quote instanceof \Magento\Quote\Api\Data\CartInterface) {
            return;
        }

        $selectedMode = $this->helper->getQuoteLimitationMode($quote->getStoreId());
        if ($selectedMode === Helper::QUOTE_LIMITATION_MODE_OVERLOADING) {
            return;
        }

        if ($selectedMode === Helper::QUOTE_LIMITATION_MODE_RESTRICTION) {
            /** @var \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $shippingAddress */
            $shippingAddress = $quote->getShippingAddress();
            if (!$shippingAddress) {
                return;
            }

            $deliveryDay         = $shippingAddress->getExtensionAttributes()->getDeliveryDay();
            $deliveryHoursFrom   = (int)$shippingAddress->getExtensionAttributes()->getDeliveryHoursFrom();
            $deliveryMinutesFrom = (int)$shippingAddress->getExtensionAttributes()->getDeliveryMinutesFrom();
            $deliveryHoursTo     = (int)$shippingAddress->getExtensionAttributes()->getDeliveryHoursTo();
            $deliveryMinutesTo   = (int)$shippingAddress->getExtensionAttributes()->getDeliveryMinutesTo();
            $deliveryTime        = (int)$shippingAddress->getExtensionAttributes()->getDeliveryTime();

            $shippingMethod = $shippingAddress->getShippingMethod();
            if (!$shippingMethod) {
                return;
            }

            // Temp: skip in-store pickup method
            if ($shippingMethod === 'instore_pickup') {
                return;
            }

            if ($this->deliveryManager->deliveryTimeDisabledOnAllProducts($quote)) {
                $this->cleanDeliveryDateData($shippingAddress);
                return;
            }

            try {
                $deliveryOption = $this->findDeliveryOption($quote);
            } catch (DeliveryTimeException $deliveryTimeException) {
                $this->cleanDeliveryDateData($shippingAddress);
                return; // Delivery option does not exist, skip further validation
            }

            if (!$deliveryDay) {
                if ($deliveryOption && $deliveryOption->getId()) {
                    if ($this->helper->isDeliveryDateRequired()) {
                        $error = $deliveryOption->getDeliveryDateRequiredErrorMessage() ?? __(
                            'Delivery date is required. Please select delivery date and proceed.'
                        );
                        throw new ValidatorException(
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

            $deliveryDateData = $this->deliveryDateDataFactory->create();
            $deliveryDateData->setShippingMethod($shippingMethod)
                             ->setDeliveryDay((string)$deliveryDay)
                             ->setDeliveryHoursFrom($deliveryHoursFrom)
                             ->setDeliveryMinutesFrom($deliveryMinutesFrom)
                             ->setDeliveryHoursTo($deliveryHoursTo)
                             ->setDeliveryMinutesTo($deliveryMinutesTo)
                             ->setDeliveryTime($deliveryTime)
                             ->setDeliveryOption($deliveryOption->getId());

            $this->validatorPool->validateAll($deliveryDateData, $deliveryOption);

            $day = $this->getSelectedDay($deliveryDay, $deliveryHoursTo, $deliveryMinutesTo);
            $this->validateLimitsExceeded(
                $deliveryOption,
                $day,
                $quote,
                $deliveryHoursFrom,
                $deliveryHoursTo,
                $deliveryMinutesFrom,
                $deliveryMinutesTo
            );
        }
    }

    /**
     * @param string|null $deliveryDay
     * @param int|null $deliveryHoursTo
     * @param int|null $deliveryMinutesTo
     * @return bool|DateTime
     */
    private function getSelectedDay($deliveryDay, int $deliveryHoursTo = null, int $deliveryMinutesTo = null)
    {
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
        DateTime       $day,
        Quote          $quote,
        int            $deliveryHoursFrom = null,
        int            $deliveryHoursTo = null,
        int            $deliveryMinutesFrom = null,
        int            $deliveryMinutesTo = null
    ) {
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
            throw new ValidatorException(
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
     * @return DeliveryOptionInterface
     * @throws \Exception|DeliveryTimeException
     */
    private function findDeliveryOption(Quote $quote): DeliveryOptionInterface
    {
        $this->deliveryManager->setDaysOffset($this->deliveryManager->calculateDaysOffset($quote));
        $this->deliveryManager->setQuote($quote);

        $shippingMethod          = $quote->getShippingAddress()->getShippingMethod();
        $additionalData          = [
            'shipping_method' => $shippingMethod
        ];
        $deliveryOptionCondition = $this->deliveryManager->convertQuoteToConditions($quote, $additionalData);
        /** @var DeliveryOption $deliveryOption */
        $deliveryOption = $this->deliveryManager->getDeliveryOptionByConditions(
            $deliveryOptionCondition
        );

        if ($deliveryOption === null) {
            throw new DeliveryTimeException(__('Unable to locate suitable delivery option.'));
        }

        return $deliveryOption;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @return void
     * @throws LocalizedException
     */
    private function cleanDeliveryDateData(\Magento\Quote\Api\Data\AddressInterface $shippingAddress): void
    {
        $this->queueManager->cleanDeliveryDateDataByQuoteAddress($shippingAddress);
    }
}
