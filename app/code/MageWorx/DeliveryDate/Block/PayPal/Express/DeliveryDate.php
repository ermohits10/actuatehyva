<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\PayPal\Express;

class DeliveryDate extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var \MageWorx\DeliveryDate\Model\CheckoutConfigProvider\General
     */
    private $configProvider;

    /**
     * DeliveryDate constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \MageWorx\DeliveryDate\Model\CheckoutConfigProvider\General $generalConfigProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \MageWorx\DeliveryDate\Model\CheckoutConfigProvider\General $generalConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->configProvider = $generalConfigProvider;
    }

    /**
     * URLs with secure/un-secure protocol switching
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        if (!array_key_exists('_secure', $params)) {
            $params['_secure'] = $this->getRequest()->isSecure();
        }

        return parent::getUrl($route, $params);
    }

    /**
     * @return array
     */
    public function getDeliveryDataConfig()
    {
        $data = $this->configProvider->getConfig();

        return $data;
    }

    /**
     * @param \Exception $e
     */
    public function logException(\Exception $e)
    {
        $this->_logger->error($e->getMessage());
    }
}
