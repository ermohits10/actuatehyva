<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Queue;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

class Reserve extends Action
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Reserve constructor.
     *
     * @param Context $context
     * @param QueueRepositoryInterface $queueRepository
     * @param QueueManagerInterface $queueManager
     * @param CartRepositoryInterface $cartRepository
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        QueueRepositoryInterface $queueRepository,
        QueueManagerInterface $queueManager,
        CartRepositoryInterface $cartRepository,
        Session $checkoutSession,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->queueRepository = $queueRepository;
        $this->queueManager    = $queueManager;
        $this->cartRepository  = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->logger          = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$quote) {
            return $result->setData(['success' => false]);
        }

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress || !$shippingAddress->getId()) {
            return $result->setData(['success' => false]);
        }

        try {
            $deliveryDay       = $this->getRequest()->getParam('delivery_day');
            $deliveryTime      = (string)$this->getRequest()->getParam('delivery_time');
            $deliveryTimeParts = explode('_', $deliveryTime);
            $deliveryTimeFrom  = !empty($deliveryTimeParts[0]) ? $deliveryTimeParts[0] : null;
            $deliveryTimeTo    = !empty($deliveryTimeParts[1]) ? $deliveryTimeParts[1] : null;

            $deliveryHoursFrom   = $this->getPart($deliveryTimeFrom, 0);
            $deliveryMinutesFrom = $this->getPart($deliveryTimeFrom, 1);
            $deliveryHoursTo     = $this->getPart($deliveryTimeTo, 0);
            $deliveryMinutesTo   = $this->getPart($deliveryTimeTo, 1);

            $deliveryComment  = $this->getRequest()->getParam('delivery_comment');
            $deliveryOptionId = $this->getRequest()->getParam('delivery_option_id');

            $shippingAddressExtension = $shippingAddress->getExtensionAttributes();
            $shippingAddressExtension->setDeliveryDay($deliveryDay)
                                     ->setDeliveryTime($deliveryTime)
                                     ->setDeliveryHoursFrom($deliveryHoursFrom)
                                     ->setDeliveryMinutesFrom($deliveryMinutesFrom)
                                     ->setDeliveryMinutesTo($deliveryMinutesTo)
                                     ->setDeliveryHoursTo($deliveryHoursTo)
                                     ->setDeliveryComment($deliveryComment)
                                     ->setDeliveryOptionId($deliveryOptionId);

            $this->cartRepository->save($quote);

            return $result->setData(['success' => true]);

            // Another way
//            $shippingAddressId = $shippingAddress->getId();
//            /** @var QueueDataInterface|\Magento\Framework\Model\AbstractModel $queue */
//            $queue = $this->queueManager->getByQuoteAddressId($shippingAddressId);
//            if (!$queue || !$queue->getEntityId()) {
//                $queue = $this->queueRepository->getEmptyEntity();
//            }
//
//            $shippingMethod       = $shippingAddress->getShippingMethod();
//            $shippingMethodsParts = explode('_', $shippingMethod);
//            $carrier              = array_pop($shippingMethodsParts);
//            $queueData            = [
//                QueueDataInterface::CARRIER_KEY            => $carrier,
//                QueueDataInterface::SHIPPING_METHOD_KEY    => $shippingMethod,
//                QueueDataInterface::STORE_ID_KEY           => $quote->getStoreId(),
//                QueueDataInterface::DELIVERY_TIME_TO_KEY   => $deliveryTimeTo,
//                QueueDataInterface::DELIVERY_TIME_FROM_KEY => $deliveryTimeFrom,
//                QueueDataInterface::DELIVERY_DAY_KEY       => $deliveryDay,
//                QueueDataInterface::QUOTE_ADDRESS_ID_KEY   => $shippingAddressId
//            ];
//
//            $queue->addData($queueData);
//            $this->queueRepository->save($queue);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            return $result->setData(['success' => false]);
        }

        return $result->setData(['success' => true]);
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
