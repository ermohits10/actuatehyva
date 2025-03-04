<?php

namespace Actuate\ReliantDirectTheme\Model;

use Actuate\ReliantDirectTheme\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

class DefaultPaymentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Data
     */
    protected Data $configHelper;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param Data $configHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $configHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $config['default_payment_method'] = $this->configHelper->getDefaultPaymentMethod() ?? null;
        return $config;
    }
}
