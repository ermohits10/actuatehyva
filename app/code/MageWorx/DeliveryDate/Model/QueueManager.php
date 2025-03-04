<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryOptionDataInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Api\DeliveryDateValidatorPoolInterface;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface;
use MageWorx\DeliveryDate\Exceptions\QueueException;
use MageWorx\DeliveryDate\Helper\Data as Helper;

class QueueManager implements QueueManagerInterface
{
    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var DeliveryManagerInterface
     */
    private $deliveryManager;

    /**
     * @var DeliveryDateValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @param QueueRepositoryInterface $queueRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param DeliveryManagerInterface $deliveryManager
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param Helper $helper
     */
    public function __construct(
        QueueRepositoryInterface           $queueRepository,
        SearchCriteriaBuilder              $searchCriteriaBuilder,
        FilterBuilder                      $filterBuilder,
        DeliveryManagerInterface           $deliveryManager,
        CartRepositoryInterface            $cartRepository,
        MaskedQuoteIdToQuoteIdInterface    $maskedQuoteIdToQuoteId,
        DeliveryDateValidatorPoolInterface $validatorPool,
        Helper                             $helper
    ) {
        $this->queueRepository        = $queueRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->filterBuilder          = $filterBuilder;
        $this->deliveryManager        = $deliveryManager;
        $this->cartRepository         = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->validatorPool          = $validatorPool;
        $this->helper                 = $helper;
    }

    /**
     * @param int $addressId
     * @return QueueDataInterface|null
     */
    public function getByQuoteAddressId($addressId): ?QueueDataInterface
    {
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(QueueDataInterface::QUOTE_ADDRESS_ID_KEY, $addressId)
            ->setCurrentPage(1)
            ->setPageSize(1)
            ->create();

        $queueList  = $this->queueRepository->getList($searchCriteria, true);
        $queueItems = $queueList->getItems();
        /** @var QueueDataInterface|null $queue */
        $queue = array_shift($queueItems);

        return $queue;
    }

    /**
     * @param int $addressId
     * @return QueueDataInterface
     */
    public function getByOrderAddressId($addressId): ?QueueDataInterface
    {
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(QueueDataInterface::ORDER_ADDRESS_ID_KEY, $addressId)
            ->setCurrentPage(1)
            ->setPageSize(1)
            ->create();

        $queueList  = $this->queueRepository->getList($searchCriteria, true);
        $queueItems = $queueList->getItems();
        /** @var QueueDataInterface|null $queue */
        $queue = array_shift($queueItems);

        return $queue;
    }

    /**
     * @param QueueDataInterface $queue
     * @param array $data
     * @return QueueManagerInterface
     * @throws QueueException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateQueue(QueueDataInterface $queue, array $data = []): QueueManagerInterface
    {
        $queue->addData($data);
        $this->queueRepository->save($queue);

        return $this;
    }

    /**
     * Check: is a selected delivery time is over a limit?
     *
     * @param \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $deliveryOption
     * @param \DateTime $day
     * @param int $hoursFrom
     * @param int $hoursTo
     * @param int $minutesFrom
     * @param int $minutesTo
     * @return bool
     */
    public function isLimitExceeded(
        \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $deliveryOption,
        \DateTime                                          $day,
        $hoursFrom = 0,
        $hoursTo = 0,
        $minutesFrom = 0,
        $minutesTo = 0
    ): bool {
        try {
            $quotesScope = $deliveryOption->getQuotesScope();
            if ($quotesScope == $deliveryOption::QUOTES_SCOPE_UNLIMITED) {
                return false;
            } elseif ($quotesScope == $deliveryOption::QUOTES_SCOPE_PER_DAY) {
                $limitTemplates = $deliveryOption->getLimitsSerialized();
                $dayLimits      = $limitTemplates[$deliveryOption::LIMITS_GENERAL];
            } else {
                $dayLimits = $deliveryOption->getLimit($day);
            }

            if (empty($dayLimits)) {
                throw new ValidatorException(__('Limits is unavailable for the whole date %1', $day->format('Y-m-d')));
            }

            if (empty($dayLimits['time_limits'])) {
                $this->checkIsLimitExceededByDay($deliveryOption, $day, $dayLimits);
            } else {
                $this->checkIsLimitExceededByDayAndTime(
                    $deliveryOption,
                    $day,
                    $dayLimits,
                    $hoursFrom,
                    $hoursTo,
                    $minutesFrom,
                    $minutesTo
                );
            }
        } catch (LocalizedException $exception) {
            return true;
        }

        return false;
    }

    /**
     * @param \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $deliveryOption
     * @param \DateTime $day
     * @param array $dayLimits
     * @throws LocalizedException
     */
    private function checkIsLimitExceededByDay(
        \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $deliveryOption,
        \DateTime                                          $day,
        array                                              $dayLimits
    ) {
        // If daily limit was not set it is endless
        if ($dayLimits['daily_quotes'] === ''
            || $dayLimits['daily_quotes'] === DeliveryOptionDataInterface::QUOTES_UNLIMITED
        ) {
            return;
        }

        $dailyQuotesLimit    = (int)$dayLimits['daily_quotes'];
        $searchResult        = $deliveryOption->getReservedQuotesForDay($day);
        $reservedQuotesCount = $searchResult->getTotalCount();

        if ($reservedQuotesCount >= $dailyQuotesLimit) {
            throw new ValidatorException(
                __('Sorry, selected date is not available, please select another delivery date')
            );
        }
    }

    /**
     * @param \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $deliveryOption
     * @param \DateTime $day
     * @param array $dayLimits
     * @param int $hoursFrom
     * @param int $hoursTo
     * @param int $minutesFrom
     * @param int $minutesTo
     * @throws LocalizedException
     */
    private function checkIsLimitExceededByDayAndTime(
        \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $deliveryOption,
        \DateTime                                          $day,
        array                                              $dayLimits,
        $hoursFrom = 0,
        $hoursTo = 0,
        $minutesFrom = 0,
        $minutesTo = 0
    ) {
        $timeLimits          = empty($dayLimits['time_limits']) ? [] : $dayLimits['time_limits'];
        $dailyQuotesLimit    = empty($dayLimits['daily_quotes'])
        || $dayLimits['daily_quotes'] === DeliveryOptionInterface::QUOTES_UNLIMITED
            ? null
            : (int)$dayLimits['daily_quotes'];
        $fullyCompatibleTime = null;
        $compatibleTime      = [];

        foreach ($timeLimits as $index => $timeLimit) {
            $fromToParts = $this->helper->parseFromToPartsFromTimeLimitTemplate($timeLimit);

            if ($fromToParts['from']['hours'] == $hoursFrom &&
                $fromToParts['from']['minutes'] == $minutesFrom &&
                $fromToParts['to']['hours'] == $hoursTo &&
                $fromToParts['to']['minutes'] == $minutesTo
            ) {
                $fullyCompatibleTime = $timeLimit;
                break;
            }

            $timeFromInMinutes = $hoursFrom * 60 + $minutesFrom;
            $timeToInMinutes   = $hoursTo * 60 + $minutesTo;

            if ($fromToParts['from']['in_minutes'] <= $timeFromInMinutes &&
                $fromToParts['to']['in_minutes'] >= $timeToInMinutes
            ) {
                $compatibleTime[$index] = $timeLimit;
            }
        }

        if ($fullyCompatibleTime === null) {
            if (empty($compatibleTime)) {
                throw new ValidatorException(__('Compatible time limit not found'));
            }
            $fullyCompatibleTime = array_pop($compatibleTime);
        }

        $timeQuoteLimit = empty($fullyCompatibleTime['quote_limit']) ? null : (int)$fullyCompatibleTime['quote_limit'];
        if (!$timeQuoteLimit && $timeQuoteLimit !== null) {
            throw new ValidatorException(__('Compatible time limit has no quote limits'));
        }

        if ($dailyQuotesLimit !== null) {
            if ($timeQuoteLimit === null) {
                $quoteLimit = $dailyQuotesLimit;
            } else {
                // If the daily quote is limited validate it using a minimum value
                $quoteLimit = $dailyQuotesLimit ? min($dailyQuotesLimit, $timeQuoteLimit) : $timeQuoteLimit;
            }

            $searchResult        = $deliveryOption->getReservedQuotesForTimeLimit($day, $fullyCompatibleTime);
            $reservedQuotesCount = $searchResult->getTotalCount();

            if ($reservedQuotesCount >= $quoteLimit) {
                throw new ValidatorException(__('Time limit is not available'));
            }
        }
    }

    /**
     * Return selected delivery date for specified cart
     *
     * @param int $cartId
     * @return QueueDataInterface|null
     */
    public function getSelectedDeliveryDateByCartId(int $cartId): ?QueueDataInterface
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote             = $this->cartRepository->get($cartId);
        $shippingAddressId = (int)$quote->getShippingAddress()->getId();

        return $this->getByQuoteAddressId($shippingAddressId);
    }

    /**
     * Return selected delivery date for specified guest cart
     *
     * @param string $cartId
     * @return QueueDataInterface|null
     */
    public function getSelectedDeliveryDateByGuestCartId(string $cartId): ?QueueDataInterface
    {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($cartId);

        return $this->getSelectedDeliveryDateByCartId($cartId);
    }

    /**
     * Set delivery date and time for cart
     *
     * @param int $cartId
     * @param DeliveryDateDataInterface $deliveryDateData
     * @return DeliveryDateDataInterface|null
     * @throws LocalizedException|QueueException
     */
    public function setDeliveryDateForCart(
        int                       $cartId,
        DeliveryDateDataInterface $deliveryDateData,
        ?AddressInterface         $address
    ): ?QueueDataInterface {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $this->deliveryManager->setQuote($quote);

        $queue = $this->getSelectedDeliveryDateByCartId($cartId);
        if (empty($queue) || !$queue->getEntityId()) {
            $queue = $this->queueRepository->getEmptyEntity();
        }

        if (empty($deliveryDateData->getDeliveryDay())) {
            if ($queue->getEntityId()) {
                $this->queueRepository->delete($queue);
            }

            return $queue;
        }

        // Set data
        $day          = new \DateTime($deliveryDateData->getDeliveryDay());
        $deliveryTime = $deliveryDateData->getDeliveryTime();
        if ($deliveryTime) {
            $parts       = $this->helper->parseFromToPartsFromTimeString($deliveryTime);
            $hoursFrom   = $parts['from']['hours'] ?? '';
            $minutesFrom = $parts['from']['minutes'] ?? '';
            $hoursTo     = $parts['to']['hours'] ?? '';
            $minutesTo   = $parts['to']['minutes'] ?? '';
            $deliveryDateData->setDeliveryHoursFrom($hoursFrom);
            $deliveryDateData->setDeliveryMinutesFrom($minutesFrom);
            $deliveryDateData->setDeliveryHoursTo($hoursTo);
            $deliveryDateData->setDeliveryMinutesTo($minutesTo);
        } else {
            $hoursFrom   = $deliveryDateData->getDeliveryHoursFrom();
            $minutesFrom = $deliveryDateData->getDeliveryMinutesFrom();
            $hoursTo     = $deliveryDateData->getDeliveryHoursTo();
            $minutesTo   = $deliveryDateData->getDeliveryMinutesTo();
        }

        $shippingMethod = $deliveryDateData->getShippingMethod() ?? $quote->getShippingAddress()->getShippingMethod();

        $additionalData          = [
            'shipping_method' => $shippingMethod,
            'address'         => $address
        ];
        $deliveryOptionCondition = $this->deliveryManager->convertQuoteToConditions($quote, $additionalData);

        $deliveryOption = $this->deliveryManager->getDeliveryOptionByConditions(
            $deliveryOptionCondition
        );

        if (!$deliveryOption) {
            throw new ValidatorException(
                __('Delivery dates are not available for this cart.')
            );
        }

        $this->validatorPool->validateAll($deliveryDateData, $deliveryOption);

        $dayLimits = $deliveryOption->getLimit($day);
        if (!empty($dayLimits['time_limits'])) {
            // Calculate using day and time
            $this->checkIsLimitExceededByDayAndTime(
                $deliveryOption,
                $day,
                $dayLimits,
                ...
                [$hoursFrom, $hoursTo, $minutesFrom, $minutesTo]
            );

            if (!$deliveryTime) {
                $from = implode(QueueDataInterface::TIME_DELIMITER, [$hoursFrom, $minutesFrom]);
                $to   = implode(QueueDataInterface::TIME_DELIMITER, [$hoursTo, $minutesTo]);

                $deliveryTime = $from . '_' . $to;
            }
        } elseif (!empty($dayLimits)) {
            // Calculate by day only
            $this->checkIsLimitExceededByDay($deliveryOption, $day, $dayLimits);
        } else {
            throw new ValidatorException(
                __('The selected delivery date is not available. Please select a different date and try again.')
            );
        }

        $shippingMethodParts = explode('_', (string)$shippingMethod);
        $carrier             = array_shift($shippingMethodParts);

        $this->updateQueue($queue, [
            QueueDataInterface::DELIVERY_OPTION_ID_KEY    => $deliveryOption->getId(),
            QueueDataInterface::DELIVERY_COMMENT_KEY      => $deliveryDateData->getDeliveryComment(),
            QueueDataInterface::DELIVERY_HOURS_FROM_KEY   => $deliveryDateData->getDeliveryHoursFrom(),
            QueueDataInterface::DELIVERY_MINUTES_FROM_KEY => $deliveryDateData->getDeliveryMinutesFrom(),
            QueueDataInterface::DELIVERY_HOURS_TO_KEY     => $deliveryDateData->getDeliveryHoursTo(),
            QueueDataInterface::DELIVERY_MINUTES_TO_KEY   => $deliveryDateData->getDeliveryMinutesTo(),
            QueueDataInterface::DELIVERY_DAY_KEY          => $deliveryDateData->getDeliveryDay(),
            QueueDataInterface::DELIVERY_TIME_KEY         => $deliveryTime,
            QueueDataInterface::SHIPPING_METHOD_KEY       => $shippingMethod,
            QueueDataInterface::CARRIER_KEY               => $carrier,
            QueueDataInterface::STORE_ID_KEY              => $quote->getStoreId(),
            QueueDataInterface::QUOTE_ADDRESS_ID_KEY      => $quote->getShippingAddress()->getId()
        ]);

        $this->updateExtensionAttributesInQuoteAddress($quote->getShippingAddress(), $queue);
        $this->cartRepository->save($quote);

        return $queue;
    }

    /**
     * @param AddressInterface $address
     * @param QueueDataInterface $queue
     * @return QueueManagerInterface
     */
    public function updateExtensionAttributesInQuoteAddress(
        AddressInterface $address,
        QueueDataInterface $queue
    ): QueueManagerInterface {
        $deliveryDay         = $queue->getDeliveryDay();
        $deliveryHoursFrom   = $queue->getDeliveryHoursFrom() ?? '00';
        $deliveryHoursTo     = $queue->getDeliveryHoursTo() ?? '00';
        $deliveryMinutesFrom = $queue->getDeliveryMinutesFrom() ?? '00';
        $deliveryMinutesTo   = $queue->getDeliveryMinutesTo() ?? '00';
        $deliveryComment     = $queue->getDeliveryComment() ?? '';
        $deliveryOptionId    = $queue->getDeliveryOption();

        /** @var \Magento\Quote\Api\Data\AddressExtension $extension */
        $extension = $address->getExtensionAttributes();

        $extension->setDeliveryDay($deliveryDay);
        $extension->setDeliveryHoursFrom($deliveryHoursFrom);
        $extension->setDeliveryMinutesFrom($deliveryMinutesFrom);
        $extension->setDeliveryHoursTo($deliveryHoursTo);
        $extension->setDeliveryMinutesTo($deliveryMinutesTo);
        $extension->setDeliveryComment($deliveryComment);
        $extension->setDeliveryOptionId($deliveryOptionId);

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

        return $this;
    }

    /**
     * Set delivery date and time for guest cart
     *
     * @param string $cartId
     * @param DeliveryDateDataInterface $deliveryDateData
     * @return DeliveryDateDataInterface|null
     * @throws LocalizedException|QueueException
     */
    public function setDeliveryDateForGuestCart(
        string                    $cartId,
        DeliveryDateDataInterface $deliveryDateData,
        ?AddressInterface         $address
    ): ?QueueDataInterface {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($cartId);

        return $this->setDeliveryDateForCart($cartId, $deliveryDateData, $address);
    }

    /**
     * Remove queue for quote address
     *
     * @param int $addressId
     * @return void
     * @throws LocalizedException
     */
    public function cleanQueueByQuoteAddressId(int $addressId): void
    {
        try {
            $queue = $this->getByQuoteAddressId($addressId);
        } catch (NoSuchEntityException $exception) {
            return; // Queue does not exist
        }

        if ($queue instanceof QueueDataInterface) {
            $this->queueRepository->delete($queue);
        }
    }

    /**
     * Clean queue data linked with quote address; Clean queue data in address;
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @return void
     * @throws LocalizedException
     */
    public function cleanDeliveryDateDataByQuoteAddress(\Magento\Quote\Api\Data\AddressInterface $shippingAddress): void
    {
        if ($shippingAddress->getId()) {
            $this->cleanQueueByQuoteAddressId((int)$shippingAddress->getId());
        }

        /** @var \Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes */
        $extensionAttributes = $shippingAddress->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            $extensionAttributes->setDeliveryOptionId(null);
            $extensionAttributes->setDeliveryTime(null);
            $extensionAttributes->setDeliveryComment(null);
            $extensionAttributes->setDeliveryMinutesTo(null);
            $extensionAttributes->setDeliveryHoursTo(null);
            $extensionAttributes->setDeliveryMinutesFrom(null);
            $extensionAttributes->setDeliveryHoursFrom(null);
            $extensionAttributes->setDeliveryDay(null);
        }
    }
}
