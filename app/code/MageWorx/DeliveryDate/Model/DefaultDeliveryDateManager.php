<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model;

use DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Api\DefaultDeliveryDateManagerInterface;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use Psr\Log\LoggerInterface;

class DefaultDeliveryDateManager implements DefaultDeliveryDateManagerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ValidateDeliveryDateAvailability constructor.
     *
     * @param Helper $helper
     * @param TimezoneInterface $timezone
     * @param QueueManagerInterface $queueManager
     * @param DeliveryManagerInterface $deliveryManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Helper $helper,
        TimezoneInterface $timezone,
        QueueManagerInterface $queueManager,
        DeliveryManagerInterface $deliveryManager,
        LoggerInterface $logger
    ) {
        $this->helper          = $helper;
        $this->timezone        = $timezone;
        $this->queueManager    = $queueManager;
        $this->deliveryManager = $deliveryManager;
        $this->logger          = $logger;
    }

    /**
     * Adds default delivery date to the quote if it could be found using selected shipping method
     *
     * @param CartInterface|Quote $quote
     * @return void
     */
    public function addToQuote(CartInterface $quote): void
    {
        if (!$this->helper->isNeedToSetDefaultDeliveryDate($quote->getStoreId())) {
            return;
        }

        /** @var \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress) {
            return;
        }

        $deliveryDay = $shippingAddress->getExtensionAttributes()->getDeliveryDay();
        if ($deliveryDay) {
            // Delivery date is set by the user
            return;
        }

        try {
            $currentDayTime = $this->getCurrentDateTime();
            $shippingMethod = $shippingAddress->getShippingMethod();
            if (!$shippingMethod) {
                return;
            }
            $deliveryOption = $this->findDeliveryOption($quote, $shippingMethod, $currentDayTime);
            $dayLimits      = $deliveryOption->getDayLimits();
            if (empty($dayLimits)) {
                // We cant set delivery date if it is totally unavailable
                return;
            }

            $firstAvailableLimit = array_shift($dayLimits);
            $extension           = $shippingAddress->getExtensionAttributes();

            $extension->setDeliveryDay($firstAvailableLimit['date']);

            if (!empty($firstAvailableLimit['time_limits'])) {
                $firstAvailableTimeLimit = array_shift($firstAvailableLimit['time_limits']);
                $from                    = explode(':', (string)$firstAvailableTimeLimit['from']);
                $to                      = explode(':', (string)$firstAvailableTimeLimit['to']);
                $extension->setDeliveryHoursFrom(
                    $from[0] ?? '00'
                );
                $extension->setDeliveryMinutesFrom(
                    $from[1] ?? '00'
                );
                $extension->setDeliveryHoursTo($to[0] ?? '00');
                $extension->setDeliveryMinutesTo($to[1] ?? '00');
            }
            $extension->setDeliveryComment(__('Delivery Date was set automatically'));
            $extension->setDeliveryOptionId($deliveryOption->getId());

            $extension->setDeliveryTime(
                implode(
                    QueueDataInterface::TIME_DELIMITER,
                    [
                        $extension->getDeliveryHoursFrom(),
                        $extension->getDeliveryMinutesFrom(),
                    ]
                )
                . '_' .
                implode(
                    QueueDataInterface::TIME_DELIMITER,
                    [
                        $extension->getDeliveryHoursTo(),
                        $extension->getDeliveryMinutesTo(),
                    ]
                )
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            $this->logger->critical($exception->getTraceAsString());

            // Did not break a checkout process in case of any error
            return;
        }
    }

    /**
     * @return DateTime
     * @throws \Exception
     */
    private function getCurrentDateTime(): DateTime
    {
        $currentDayTime = new DateTime();
        $storeTimeZone  = new \DateTimeZone($this->timezone->getConfigTimezone());
        $currentDayTime->setTimezone($storeTimeZone);

        return $currentDayTime;
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
        $customerGroupId = $quote->getCustomer()->getGroupId();
        $storeId         = $quote->getStoreId();
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
