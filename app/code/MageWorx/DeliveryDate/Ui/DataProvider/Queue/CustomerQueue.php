<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\DataProvider\Queue;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;

class CustomerQueue extends AbstractDataProvider
{
    /**
     * @var QueueCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var QueueManagerInterface
     */
    protected $queueManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var DeliveryManagerInterface
     */
    protected $deliveryManager;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * CustomerQueue constructor.
     *
     * @param QueueCollectionFactory $collectionFactory
     * @param HttpRequest $request
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param QueueManagerInterface $queueManager
     * @param ManagerInterface $messageManager
     * @param CustomerSession $customerSession
     * @param DeliveryManagerInterface $deliveryManager
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        QueueCollectionFactory $collectionFactory,
        HttpRequest $request,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository,
        QueueManagerInterface $queueManager,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        DeliveryManagerInterface $deliveryManager,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collection        = $collectionFactory->create();
        $this->request           = $request;
        $this->orderRepository   = $orderRepository;
        $this->quoteRepository   = $quoteRepository;
        $this->queueManager      = $queueManager;
        $this->messageManager    = $messageManager;
        $this->customerSession   = $customerSession;
        $this->deliveryManager   = $deliveryManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     * @throws LocalizedException
     */
    public function getData()
    {
        $orderId = $this->request->getParam($this->requestFieldName);
        if (!$orderId) {
            return [];
        }

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('Unable to locate the order with id %1', $orderId));

            return [];
        }

        $address    = $order->getShippingAddress();
        $customerId = $order->getCustomerId();

        if ($this->customerSession->getCustomerId() !== $customerId) {
            throw new LocalizedException(__('Unauthorized access.'));
        }

        $orderAddressId = $address->getEntityId();

        try {
            $queue  = $this->queueManager->getByOrderAddressId($orderAddressId);
            $quote  = $this->quoteRepository->get($order->getQuoteId());
            $limits = $this->deliveryManager->getAvailableLimitsForQuote($quote);
        } catch (NoSuchEntityException $exception) {
            $limits = [];
        }

        $dayLimitsByMethod = !empty($limits[$order->getShippingMethod()]['day_limits'])
            ? $limits[$order->getShippingMethod()]['day_limits']
            : [];

        $deliveryOptionId = !empty($limits[$order->getShippingMethod()]['entity_id']) ?
            $limits[$order->getShippingMethod()]['entity_id'] :
            null;

        return [
            'queue'              => [
                'entity_id'       => !empty($queue) ? $queue->getEntityId() : null,
                'customer_id'     => $customerId,
                'order_id'        => $orderId,
                'delivery_option' => $deliveryOptionId
            ],
            'day_limits'         => $dayLimitsByMethod,
            'active_time_limits' => []
        ];
    }
}
