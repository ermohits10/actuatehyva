<?php

namespace Actuate\ConfigurableProduct\Plugin\Block\Product\View\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json;

class ConfigurablePlugin
{
    private Json $json;

    /**
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * @param Configurable $subject
     * @param $result
     * @return bool|string
     */
    public function afterGetJsonConfig(
        Configurable $subject,
        $result
    ) {
        $config = $this->json->unserialize($result);
        foreach ($config as $configKey => &$configItem) {
            if ($configKey === 'prices') {
                if ($configItem['oldPrice']['amount'] !== $configItem['finalPrice']['amount']) {
                    $savePrice = (float)($configItem['oldPrice']['amount'] - $configItem['finalPrice']['amount']);
                    $baseSavePrice = (float)($configItem['baseOldPrice']['amount'] - $configItem['basePrice']['amount']);
                }
                $configItem['savePrice']['amount'] = $savePrice ?? 0;
                $configItem['baseSavePrice']['amount'] = $baseSavePrice ?? 0;
                $configItem['exVatFinalPrice']['amount'] = round(($configItem['finalPrice']['amount']/1.2), 2);
            }

            if ($configKey === 'optionPrices') {
                foreach ($configItem as &$optionProduct) {
                    if (isset($optionProduct['oldPrice']) && $optionProduct['oldPrice']['amount'] !== $optionProduct['finalPrice']['amount']) {
                        $savePrice = (float)($optionProduct['oldPrice']['amount'] - $optionProduct['finalPrice']['amount']);
                        $baseSavePrice = (float)($optionProduct['baseOldPrice']['amount'] - $optionProduct['basePrice']['amount']);
                    }
                    $optionProduct['savePrice']['amount'] = $savePrice ?? 0;
                    $optionProduct['baseSavePrice']['amount'] = $baseSavePrice ?? 0;
                    if (isset($optionProduct['finalPrice'])) {
                        $optionProduct['exVatFinalPrice']['amount'] = round(($optionProduct['finalPrice']['amount']/1.2), 2);
                    }
                }
            }

            if ($configKey === 'attributes') {
                foreach ($configItem as &$optionProduct) {
                    array_multisort(
                        array_column($optionProduct['options'], 'label'),
                        SORT_ASC,
                        $optionProduct['options']
                    );
                }
            }
        }

        return $this->json->serialize($config);
    }
}
