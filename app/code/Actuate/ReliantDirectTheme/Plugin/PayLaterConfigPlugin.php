<?php

namespace Actuate\ReliantDirectTheme\Plugin;

use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\PayLaterConfig;

class PayLaterConfigPlugin
{
    private ConfigFactory $configFactory;
    private \Magento\Paypal\Model\Config $config;

    /**
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        ConfigFactory $configFactory
    ) {
        $this->config = $configFactory->create();
    }

    /**
     * @param PayLaterConfig $subject
     * @param $result
     * @param string $placement
     * @return bool
     */
    public function afterIsEnabled(
        PayLaterConfig $subject,
        $result,
        string $placement
    ): bool {
        if ($result === false) {
            $payLaterActive = (boolean)$this->config->getPayLaterConfigValue('experience_active');
            $isPayLaterEnabled = (boolean)$this->config->getPayLaterConfigValue('enabled');
            return $payLaterActive && $isPayLaterEnabled && $subject->getSectionConfig($placement, 'display');
        }

        return $result;
    }
}
