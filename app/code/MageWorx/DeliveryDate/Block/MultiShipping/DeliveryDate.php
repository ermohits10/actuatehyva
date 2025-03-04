<?php

namespace MageWorx\DeliveryDate\Block\MultiShipping;

use Magento\Framework\View\Element\Template;

/**
 * Class DeliveryDate
 *
 * Adds delivery date inputs on the multishipping checkout (select shipping method step)
 *
 * @package MageWorx\DeliveryDate\Block\MultiShipping
 */
class DeliveryDate extends Template
{
    /**
     * @var string
     */
    protected $_template = 'MageWorx_DeliveryDate::multishipping/delivery-date.phtml';

    /**
     * @var \MageWorx\DeliveryDate\Model\CheckoutConfigProvider\General
     */
    private $configProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var array
     */
    private $config = [];

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
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
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
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getEntity(): \Magento\Quote\Model\Quote\Address
    {
        return $this->getData('entity');
    }

    /**
     * @return array
     */
    public function getDeliveryDataConfig()
    {
        if (empty($this->config)) {
            $this->config = $this->configProvider->getConfig();
        }

        return $this->config;
    }

    /**
     * @param \Exception $e
     */
    public function logException(\Exception $e)
    {
        $this->_logger->error($e->getMessage());
    }
}
