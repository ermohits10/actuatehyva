<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveQueue
 *
 * Removes item from queue in case when order was canceled
 *
 * @event order_cancel_after
 *
 */
class RemoveQueue implements ObserverInterface
{
    /**
     * @var QueueManagerInterface
     */
    private $queueManager;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateQueue constructor.
     *
     * @param QueueManagerInterface $queueManager
     * @param QueueRepositoryInterface $queueRepository
     */
    public function __construct(
        QueueManagerInterface $queueManager,
        QueueRepositoryInterface $queueRepository,
        LoggerInterface $logger
    ) {
        $this->queueManager    = $queueManager;
        $this->queueRepository = $queueRepository;
        $this->logger          = $logger;
    }

    /**
     * Remove item from queue when order canceled
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $event->getData('order');
        if (!$order) {
            return;
        }

        $shipmentsCount = $order->getShipmentsCollection()->getSize();
        if ($shipmentsCount > 0) {
            return;
        }

        if ($order->getIsVirtual()) {
            return;
        }

        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            return;
        }

        $shippingAddressId = $shippingAddress->getId();
        if (!$shippingAddressId) {
            return;
        }

        $queue = $this->queueManager->getByOrderAddressId($shippingAddressId);
        if (!$queue || !$queue->getEntityId()) {
            return;
        }

        try {
            $this->queueRepository->delete($queue);
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
