<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;
use Magento\Shipping\Model\CarrierFactory;

class Carriers implements ArrayInterface
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CarrierFactory $carrierFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CarrierFactory $carrierFactory
    ) {
        $this->scopeConfig    = $scopeConfig;
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $carriers = $this->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            $carrierTitle         = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $result[$carrierCode] = ['label' => $carrierTitle, 'value' => $carrierCode];
        }

        if (empty($result)) {
            $result = [
                'label' => [],
                'value' => []
            ];
        }

        return $result;
    }

    /**
     * Retrieve all system carriers
     *
     * @param   mixed $store
     * @return  AbstractCarrierInterface[]
     */
    public function getAllCarriers($store = null)
    {
        $carriers = [];
        $config = $this->scopeConfig->getValue(
            'carriers',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        foreach (array_keys($config) as $carrierCode) {
            $className = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/model',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );

            if (!$className || !class_exists($className)) {
                continue;
            }

            $model = $this->carrierFactory->create($carrierCode, $store);
            if ($model) {
                $carriers[$carrierCode] = $model;
            }
        }
        return $carriers;
    }
}
