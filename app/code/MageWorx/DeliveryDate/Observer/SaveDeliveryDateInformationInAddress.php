<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer;

use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface;
use MageWorx\DeliveryDate\Exceptions\QueueException;
use MageWorx\DeliveryDate\Helper\Data;

class SaveDeliveryDateInformationInAddress implements ObserverInterface
{
    const TYPE_QUOTE_ADDRESS    = 'quote';
    const TYPE_ORDER_ADDRESS    = 'order';
    const ADDRESS_TYPE_SHIPPING = 'shipping';

    /**
     * @var QueueManagerInterface
     */
    private $queueManager;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Data
     */
    private $helper;

    /**
     * SaveDeliveryDateInformationInAddress constructor.
     *
     * @param QueueManagerInterface $queueManager
     * @param QueueRepositoryInterface $queueRepository
     * @param CartRepositoryInterface $cartRepository
     * @param Http $request
     * @param State $state
     * @param Data $helper
     */
    public function __construct(
        QueueManagerInterface $queueManager,
        QueueRepositoryInterface $queueRepository,
        CartRepositoryInterface $cartRepository,
        Http $request,
        State $state,
        Data $helper
    ) {
        $this->queueManager    = $queueManager;
        $this->queueRepository = $queueRepository;
        $this->cartRepository  = $cartRepository;
        $this->request         = $request;
        $this->state           = $state;
        $this->helper          = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws QueueException
     */
    public function execute(Observer $observer)
    {
        /** @var OrderAddressInterface|QuoteAddressInterface $address */
        $address = $observer->getDataObject();
        if (!$address instanceof AddressModelInterface) {
            return;
        }

        if ($address->getAddressType() != self::ADDRESS_TYPE_SHIPPING) {
            return;
        }

        $extensionAttributes = $address->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return;
        }

        if ($this->isEditOrderFromAdminSide()) {
            /**
             * Do nothing when order address update from admin side in regular way
             * (View Order -> Edit Address)
             */
            return;
        }

        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            $this->prepareAddressForBackend($address);
        }

        $data = [];

        $deliveryDay = $extensionAttributes->getDeliveryDay();
        if (!$deliveryDay || $deliveryDay === '-1') {
            /** @var QueueDataInterface $queue */
            $queue = $this->getQueueEntity($address);
            if ($queue->getEntityId() && !$address->getData(QueueDataInterface::DELIVERY_DAY_KEY)) {
                $this->queueRepository->delete($queue);
            }

            return;
        }

        $deliveryTime = $extensionAttributes->getDeliveryTime();
        if (!$deliveryTime &&
            (
                $address->getData(QueueDataInterface::DELIVERY_HOURS_FROM_KEY) ||
                $address->getData(QueueDataInterface::DELIVERY_HOURS_TO_KEY)
            )
        ) {
            $fromHours   = (int)$address->getData(QueueDataInterface::DELIVERY_HOURS_FROM_KEY) ?: '0';
            $fromMinutes = (int)$address->getData(QueueDataInterface::DELIVERY_MINUTES_FROM_KEY) ?: '0';
            $toHours     = (int)$address->getData(QueueDataInterface::DELIVERY_HOURS_TO_KEY) ?: '24';
            $toMinutes   = (int)$address->getData(QueueDataInterface::DELIVERY_MINUTES_TO_KEY) ?: '0';

            $from = implode(QueueDataInterface::TIME_DELIMITER, [$fromHours, $fromMinutes]);
            $to   = implode(QueueDataInterface::TIME_DELIMITER, [$toHours, $toMinutes]);

            $deliveryTime = $from . '_' . $to;

            $extensionAttributes->setDeliveryHoursFrom($fromHours);
            $extensionAttributes->setDeliveryMinutesFrom($fromMinutes);
            $extensionAttributes->setDeliveryHoursTo($toHours);
            $extensionAttributes->setDeliveryMinutesTo($toMinutes);
            $extensionAttributes->setDeliveryTime($deliveryTime);
        }

        $data = $this->addDeliveryDayTimeInfo($deliveryDay, $deliveryTime, $data);

        /** @var QueueDataInterface $queue */
        $queue = $this->getQueueEntity($address);

        if ($address instanceof QuoteAddressInterface) {
            /** @var QuoteAddressInterface|\Magento\Quote\Model\Quote\Address $address */
            $data[QueueDataInterface::QUOTE_ADDRESS_ID_KEY] = $address->getId();
            $shippingMethod                                 = $address->getShippingMethod();
            $data[QueueDataInterface::STORE_ID_KEY]         = $address->getQuote()->getStoreId();
        } elseif ($address instanceof OrderAddressInterface) {
            /** @var OrderAddressInterface|\Magento\Sales\Model\Order\Address $address */
            $data[QueueDataInterface::ORDER_ADDRESS_ID_KEY] = $address->getId();
            $shippingMethod                                 = $address->getOrder()->getShippingMethod();
            $data[QueueDataInterface::STORE_ID_KEY]         = $address->getOrder()->getStoreId();
        } else {
            return;
        }

        if ($extensionAttributes->getDeliveryOptionId()) {
            $data[QueueDataInterface::DELIVERY_OPTION_ID_KEY] = $extensionAttributes->getDeliveryOptionId();
        }

        if ($extensionAttributes->getDeliveryComment()) {
            $data[QueueDataInterface::DELIVERY_COMMENT_KEY] = strip_tags($extensionAttributes->getDeliveryComment());
        }

        $data = $this->addShippingMethodData($shippingMethod, $data);

        try {
            $this->queueManager->updateQueue($queue, $data);
        } catch (QueueException $exception) {
            return;
        }
    }

    /**
     * @param OrderAddressInterface|QuoteAddressInterface $address
     * @return QuoteAddressInterface|OrderAddressInterface
     */
    private function prepareAddressForBackend($address)
    {
        $orderRequest = $this->request->getParam('order');
        if (empty($orderRequest)) {
            return $address;
        }

        $deliveryInfoFromRequest = isset($orderRequest['delivery_info']) ? $orderRequest['delivery_info'] : [];
        if (empty($deliveryInfoFromRequest) || empty($deliveryInfoFromRequest['delivery_day'])) {
            return $address;
        }

        $deliveryDay         = empty($deliveryInfoFromRequest['delivery_day']) ?
            null :
            $deliveryInfoFromRequest['delivery_day'];
        $deliveryHoursFrom   = empty($deliveryInfoFromRequest['delivery_hours_from']) ?
            null :
            $deliveryInfoFromRequest['delivery_hours_from'];
        $deliveryMinutesFrom = empty($deliveryInfoFromRequest['delivery_minutes_from']) ?
            null :
            $deliveryInfoFromRequest['delivery_minutes_from'];
        $deliveryHoursTo     = empty($deliveryInfoFromRequest['delivery_hours_to']) ?
            null :
            $deliveryInfoFromRequest['delivery_hours_to'];
        $deliveryMinutesTo   = empty($deliveryInfoFromRequest['delivery_minutes_to']) ?
            null :
            $deliveryInfoFromRequest['delivery_minutes_to'];

        $deliveryTime = $deliveryHoursFrom && $deliveryHoursTo ?
            $deliveryHoursFrom . QueueDataInterface::TIME_DELIMITER . $deliveryMinutesFrom . '_' .
            $deliveryHoursTo . QueueDataInterface::TIME_DELIMITER . $deliveryMinutesTo :
            null;

        $deliveryComment = empty($deliveryInfoFromRequest['delivery_comment']) ?
            null :
            $deliveryInfoFromRequest['delivery_comment'];

        $extensionAttributes = $address->getExtensionAttributes();
        $extensionAttributes->setDeliveryDay($deliveryDay)
                            ->setDeliveryTime($deliveryTime)
                            ->setDeliveryHoursFrom($deliveryHoursFrom)
                            ->setDeliveryMinutesFrom($deliveryMinutesFrom)
                            ->setDeliveryHoursTo($deliveryHoursTo)
                            ->setDeliveryMinutesTo($deliveryMinutesTo)
                            ->setDeliveryComment($deliveryComment);

        return $address;
    }

    /**
     * @param $address
     * @return QueueDataInterface|null
     * @throws QueueException
     */
    private function getQueueEntity($address)
    {
        if ($address instanceof QuoteAddressInterface) {
            /**
             * @var QueueDataInterface $queue
             */
            $queue = $this->queueManager->getByQuoteAddressId($address->getId());
        } elseif ($address instanceof OrderAddressInterface) {
            /**
             * @var QueueDataInterface $queue
             */
            $queue = $this->queueManager->getByOrderAddressId($address->getId());
            if (($queue === null || !$queue->getEntityId())) {
                $quoteShippingAddressId = $address->getQuoteAddressId() ?? $address->getExtensionAttributes()->getQuoteAddressId();
                if (!$quoteShippingAddressId) {
                    /** @var \Magento\Sales\model\Order $order */
                    $order                  = $address->getOrder();
                    $quoteId                = $order->getQuoteId();
                    $quote                  = $this->cartRepository->get($quoteId);
                    $quoteShippingAddress   = $quote->getShippingAddress();
                    $quoteShippingAddressId = $quoteShippingAddress->getId();
                }

                if (!$quoteShippingAddressId) {
                    throw new QueueException(__('Quote Address was not detected'));
                }
                $queue = $this->queueManager->getByQuoteAddressId($quoteShippingAddressId);
            }
        } else {
            throw new QueueException(
                __(
                    'The address must be instance of QuoteAddressInterface or OrderAddressInterface.
                    Instance of %1 provided.',
                    [
                        get_class($address)
                    ]
                )
            );
        }

        if ($queue === null) {
            $queue = $this->queueRepository->getEmptyEntity();
        }

        return $queue;
    }

    /**
     * @param string $deliveryDay
     * @param string $deliveryTime
     * @param array $data
     * @return array
     */
    private function addDeliveryDayTimeInfo($deliveryDay, $deliveryTime, array $data)
    {
        if ($deliveryTime === -1 || $deliveryTime === "-1") {
            $deliveryHoursFrom   = null;
            $deliveryMinutesFrom = null;
            $deliveryHoursTo     = null;
            $deliveryMinutesTo   = null;
        } elseif ($deliveryTime) {
            [
                $deliveryTimeFrom, $deliveryTimeTo
                ] =
                explode('_', $deliveryTime);
            $deliveryHoursFrom   = $this->helper->getPart($deliveryTimeFrom, 0);
            $deliveryMinutesFrom = $this->helper->getPart($deliveryTimeFrom, 1);
            $deliveryHoursTo     = $this->helper->getPart($deliveryTimeTo, 0);
            $deliveryMinutesTo   = $this->helper->getPart($deliveryTimeTo, 1);
        } else {
            $deliveryTimeFrom    = $deliveryTime;
            $deliveryHoursFrom   = $this->helper->getPart($deliveryTimeFrom, 0);
            $deliveryMinutesFrom = $this->helper->getPart($deliveryTimeFrom, 1);
            $deliveryHoursTo     = null;
            $deliveryMinutesTo   = null;
        }

        if ($deliveryDay === -1 || $deliveryDay === "-1") {
            $deliveryDay = null;
        }

        $data = array_merge_recursive(
            $data,
            [
                QueueDataInterface::DELIVERY_DAY_KEY          => $deliveryDay,
                QueueDataInterface::DELIVERY_HOURS_FROM_KEY   => $deliveryHoursFrom,
                QueueDataInterface::DELIVERY_MINUTES_FROM_KEY => $deliveryMinutesFrom,
                QueueDataInterface::DELIVERY_HOURS_TO_KEY     => $deliveryHoursTo,
                QueueDataInterface::DELIVERY_MINUTES_TO_KEY   => $deliveryMinutesTo
            ]
        );

        return $data;
    }

    /**
     * @param string $shippingMethod
     * @param array $data
     * @return array
     */
    private function addShippingMethodData($shippingMethod, array $data)
    {
        $shippingMethodParts                           = explode('_', (string)$shippingMethod);
        $carrier                                       = array_shift($shippingMethodParts);
        $data[QueueDataInterface::SHIPPING_METHOD_KEY] = $shippingMethod;
        $data[QueueDataInterface::CARRIER_KEY]         = $carrier;

        return $data;
    }

    /**
     * Check is order edited from the admin side
     *
     * @return bool
     */
    private function isEditOrderFromAdminSide(): bool
    {
        $regularEdit = $this->request->getModuleName() === 'sales' &&
            $this->request->getControllerName() === 'order' &&
            $this->request->getActionName() === 'addressSave';

        $mageworxOrderEdit = $this->request->getModuleName() === 'ordereditor' &&
            $this->request->getControllerName() === 'edit';

        return $regularEdit || $mageworxOrderEdit;
    }
}
