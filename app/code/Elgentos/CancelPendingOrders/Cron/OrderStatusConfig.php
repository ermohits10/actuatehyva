<?php

namespace Elgentos\CancelPendingOrders\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class OrderStatusConfig
{
    public const CANCEL_ORDERS_OLDER_THAN_PATH = 'elgentos_cancel_pending_orders/order/days',
        CANCEL_ORDER_STATUS_PATH               = 'elgentos_cancel_pending_orders/order/order_statuses',
        AUTO_ORDER_CANCEL_IS_ACTIVE_PATH       = 'elgentos_cancel_pending_orders/order/active';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * CancelPendingOrdersConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface    $timeZone
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timeZone
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->timeZone    = $timeZone;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->scopeConfig->getValue(self::AUTO_ORDER_CANCEL_IS_ACTIVE_PATH) ?? false;
    }

    /**
     * @return array
     */
    public function getOrderStatesForCancel(): array
    {
        $orderStatus = $this->scopeConfig->getValue(
            self::CANCEL_ORDER_STATUS_PATH,
            ScopeInterface::SCOPE_STORE
        );

        $orderStatus = explode(',', $orderStatus);

        return $orderStatus;
    }

    /**
     * @return string
     */
    public function getBeforeTime(): string
    {
        $days          = $this->scopeConfig->getValue(
            self::CANCEL_ORDERS_OLDER_THAN_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $daysToSeconds = $days * 86400;

        return $this->timeZone
            ->date((time() - $daysToSeconds))
            ->format('Y-m-d H:i:s');
    }
}
