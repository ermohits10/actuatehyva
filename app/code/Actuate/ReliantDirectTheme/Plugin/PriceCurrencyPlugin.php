<?php

namespace Actuate\ReliantDirectTheme\Plugin;

use Magento\Directory\Model\PriceCurrency;

class PriceCurrencyPlugin
{
    /**
     * @param PriceCurrency $subject
     * @param $amount
     * @param bool $includeContainer
     * @param int $precision
     * @param null $scope
     * @param null $currency
     * @return array
     */
    public function beforeFormat(
        PriceCurrency $subject,
        $amount,
        $includeContainer = true,
        $precision = PriceCurrency::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        return [
            $amount,
            $includeContainer,
            0,
            $scope,
            $currency
        ];
    }
}
