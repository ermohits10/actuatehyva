<?php

namespace Elgentos\CancelPendingOrders\Cron;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class CancelPendingOrders
{
    /**
     * @var CollectionFactory
     */
    public CollectionFactory $orderCollectionFactory;

    /**
     * @var OrderStatusConfig
     */
    private OrderStatusConfig $orderStatusConfig;

    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderStatusConfig $orderStatusConfig
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderStatusConfig $orderStatusConfig
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderStatusConfig      = $orderStatusConfig;
    }

    /**
     * @return $this
     */
    public function execute(): CancelPendingOrders
    {
        if (!$this->orderStatusConfig->isActive()) {
            return $this;
        }

        $cancelBeforeTime = $this->orderStatusConfig->getBeforeTime();
        $orders           = $this->orderCollectionFactory->create()
            ->addFieldToFilter('created_at', ['lteq' => $cancelBeforeTime])
            ->addFieldToFilter(
                'status',
                ['in' => $this->orderStatusConfig->getOrderStatesForCancel()]
            );
        $orders->getSelect();

        foreach ($orders as $order) {
            $order
                ->addStatusHistoryComment(
                    'Order has been canceled automatically',
                    Order::STATE_CANCELED
                )
                ->setIsVisibleOnFront(true);
            $order->cancel();
            $order->save();
        }

        return $this;
    }
}
