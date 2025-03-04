<?php
namespace ReliantDirect\PriceDecimal\Plugin\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Amount
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Format price value remove .00
     *
     * @param mixed $subject
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return float
     */
    public function afterFormatCurrency(
        \Magento\Framework\Pricing\Render\Amount $subject,
        $result,
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        try {
            if ($this->isNoPrecision($amount)){
                $precision = 0;
                return $this->priceCurrency->format($amount, $includeContainer, $precision);
            }
        } catch (\Exception $e) {
            return $result;
        }

        return $result;
    }

    /**
     * @param float $price
     * @return float
     */
    public function isNoPrecision($price) {
        if ($price > 0) {
            $priceNumber = floor((float)$price);
            $fraction = (float)$price - $priceNumber;
            if ($fraction > 0 && $fraction < 1) {
                return false;
            } else {
                return true;
            }
        }
		return false;
    }
}