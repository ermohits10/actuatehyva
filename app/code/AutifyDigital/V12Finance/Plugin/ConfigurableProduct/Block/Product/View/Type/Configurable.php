<?php
namespace AutifyDigital\V12Finance\Plugin\ConfigurableProduct\Block\Product\View\Type;

class Configurable
{

    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $jsonResult = json_decode($result, true);
        $jsonResult['config_simple_prices'] = [];
        foreach ($subject->getAllowProducts() as $simpleProduct) {
            $jsonResult['config_simple_prices'][$simpleProduct->getId()] = $simpleProduct->getFinalPrice();
        }
        $result = json_encode($jsonResult);
        return $result;
    }
}
