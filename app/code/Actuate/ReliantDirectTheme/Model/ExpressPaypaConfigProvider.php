<?php

namespace Actuate\ReliantDirectTheme\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ExpressPaypaConfigProvider implements ConfigProviderInterface
{
    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @param Data $configHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        LayoutInterface $layout
    ) {
        $this->_layout = $layout;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $paypalHtml = $this->_layout->createBlock('Magento\Paypal\Block\Express\Shortcut')
            ->setTemplate('Actuate_ReliantDirectTheme::paypal_button.phtml')
            ->toHtml();
        $config['paypal_express_button'] = $paypalHtml;
        return $config;
    }
}