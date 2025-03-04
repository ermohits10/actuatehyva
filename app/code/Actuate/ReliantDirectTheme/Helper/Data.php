<?php

namespace Actuate\ReliantDirectTheme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CONFIG_DEFAULT_PAYMENT = 'actuate_checkout/payment/default_method';

    private OrderFactory $orderFactory;

    /**
     * @param OrderFactory $orderFactory
     * @param Context $context
     */
    public function __construct(
        OrderFactory $orderFactory,
        Context $context
    ) {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDefaultPaymentMethod($storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_DEFAULT_PAYMENT, $storeId);
    }

    /**
     * @param $incrementId
     * @return Order
     */
    public function getOrderByIncrementId($incrementId)
    {
        return $this->orderFactory->create()->load($incrementId, 'increment_id');
    }
}
