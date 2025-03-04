<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Queue;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface;
use MageWorx\DeliveryDate\Exceptions\QueueException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Update extends Action
{
    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var QueueManagerInterface
     */
    private $queueManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Reserve constructor.
     *
     * @param Context $context
     * @param QueueRepositoryInterface $queueRepository
     * @param QueueManagerInterface $queueManager
     * @param OrderRepositoryInterface $orderRepository
     * @param Session $session
     * @param Helper $helper
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        QueueRepositoryInterface $queueRepository,
        QueueManagerInterface $queueManager,
        OrderRepositoryInterface $orderRepository,
        Session $session,
        Helper $helper,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->queueRepository = $queueRepository;
        $this->queueManager    = $queueManager;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $session;
        $this->helper          = $helper;
        $this->timezone        = $timezone;
        $this->logger          = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setPath('sales/order/history');

        $params = $this->getRequest()->getParams();
        if (empty($params['queue']['order_id'])) {
            $this->messageManager->addErrorMessage(
                __('Required parameter `%1` missed.', 'order_id')
            );

            return $result;
        }

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($params['queue']['order_id']);

            /** @var \Magento\Sales\Model\Order\Address $orderShippingAddress */
            $orderShippingAddress = $order->getShippingAddress();
            if ($orderShippingAddress) {
                $params['queue']['quote_address_id'] = $orderShippingAddress->getQuoteAddressId();
                $params['queue']['order_address_id'] = $orderShippingAddress->getEntityId();
            }

            $shippingMethodObject = $order->getShippingMethod(true);
            $carrier              = $shippingMethodObject->getData('carrier_code');

            $params['queue']['shipping_method'] = $order->getShippingMethod();
            $params['queue']['carrier']         = $carrier;
            $params['queue']['store_id']        = $order->getStoreId();

        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(
                __('Order with id %1 does not exists.', $params['queue']['order_id'])
            );

            return $result;
        }

        $result->setPath('sales/order/view', ['order_id' => $order->getEntityId()]);

        $oldDate = $params['queue']['delivery_date'];
        $newDate = $this->timezone->date($oldDate);
        $params['queue']['delivery_day'] = $newDate;
        $deliveryTime                    = (string)$params['queue']['delivery_time'];
        $deliveryTimeParts               = explode('_', $deliveryTime);
        $deliveryTimeFrom                = !empty($deliveryTimeParts[0]) ? $deliveryTimeParts[0] : null;
        $deliveryTimeTo                  = !empty($deliveryTimeParts[1]) ? $deliveryTimeParts[1] : null;

        $deliveryHoursFrom   = $this->getPart($deliveryTimeFrom, 0);
        $deliveryMinutesFrom = $this->getPart($deliveryTimeFrom, 1);
        $deliveryHoursTo     = $this->getPart($deliveryTimeTo, 0);
        $deliveryMinutesTo   = $this->getPart($deliveryTimeTo, 1);

        $params['queue']['delivery_hours_from']   = $deliveryHoursFrom;
        $params['queue']['delivery_hours_to']     = $deliveryHoursTo;
        $params['queue']['delivery_minutes_from'] = $deliveryMinutesFrom;
        $params['queue']['delivery_minutes_to']   = $deliveryMinutesTo;

        $this->updateQueue($params);

        return $result;
    }

    /**
     * Update existing queue
     *
     * @param array $params
     * @return bool
     * @throws LocalizedException
     */
    private function updateQueue(array $params = [])
    {
        if (empty($params)) {
            $params = $this->getRequest()->getParams();
        }

        try {
            if (empty($params['queue']['entity_id'])) {
                $queue = $this->queueRepository->getEmptyEntity();
                unset($params['queue']['entity_id']);
            } else {
                $queue = $this->queueRepository->getById($params['queue']['entity_id']);
            }
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(
                __('Queue with id %1 does not exists.', $params['queue']['entity_id'])
            );

            return false;
        }

        try {
            $this->queueManager->updateQueue($queue, $params['queue']);
        } catch (QueueException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage() . "\n" . $e->getTraceAsString());

            return false;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage() . "\n" . $e->getTraceAsString());

            return false;
        }

        $this->messageManager->addSuccessMessage(__('Queue %1 successfully updated.', $queue->getEntityId()));

        return true;
    }

    /**
     * @param string $data
     * @param int $i
     * @return int|null
     */
    public function getPart($data, $i)
    {
        if ($data && mb_stripos($data, QueueDataInterface::TIME_DELIMITER) !== null) {
            $parts = explode(QueueDataInterface::TIME_DELIMITER, $data);

            return isset($parts[$i]) ? (int)$parts[$i] : null;
        }

        return null;
    }
}
