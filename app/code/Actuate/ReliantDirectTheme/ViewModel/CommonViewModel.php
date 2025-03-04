<?php

namespace Actuate\ReliantDirectTheme\ViewModel;

use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Paypal\Model\SdkUrl;
use Magento\Store\Model\ScopeInterface;

class CommonViewModel implements ArgumentInterface
{
    private PriceCurrencyInterface $priceCurrency;
    private ScopeConfigInterface $scopeConfig;
    private SdkUrl $sdkUrl;
    private PriceCurrency $priceCurrencyModel;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param PriceCurrency $priceCurrencyModel
     * @param SdkUrl $sdkUrl
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ScopeConfigInterface $scopeConfig,
        PriceCurrency $priceCurrencyModel,
        SdkUrl $sdkUrl
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig;
        $this->sdkUrl = $sdkUrl;
        $this->priceCurrencyModel = $priceCurrencyModel;
    }

    /**
     * @param $price
     * @param int $precision
     * @return string
     */
    public function formatPrice($price, $precision = 0)
    {
        return $this->priceCurrency->format(
            $price,
            true,
            $precision
        );
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return mixed
     */
    public function getFreeDeliveryText()
    {
        return $this->getConfigValue('actuate_delivery/delivery/delivery_text');
    }

    /**
     * @return string
     */
    public function getPaypalSdkUrl(): string
    {
        if (str_contains($this->sdkUrl->getUrl(), 'merchant')) {
            return $this->sdkUrl->getUrl();
        }

        if ($this->getConfigValue('payment/paypal_paylater/merchant_id')) {
            return $this->sdkUrl->getUrl() . "&merchant-id=" . $this->getConfigValue('payment/paypal_paylater/merchant_id');
        }

        return $this->sdkUrl->getUrl();
    }

    /**
     * @return mixed|string
     */
    public function getFreeDeliveryHolidays()
    {
        return $this->getConfigValue('actuate_delivery/delivery/delivery_holidays') ?? '';
    }

    /**
     * @param $price
     * @param int $precision
     * @return string
     */
    public function convertPriceWithPrecision($price, $precision = 0)
    {
        return $this->priceCurrencyModel->getCurrency(null, null)
            ->formatPrecision($price, $precision, [], false);
    }

    /**
     * @param $stockAnalysis
     * @return array
     */
    public function getStockAnalysisData($stockAnalysis)
    {
        $stockXml = simplexml_load_string($stockAnalysis, "SimpleXMLElement", LIBXML_NOCDATA);
        $stockData = json_decode(json_encode($stockXml), true);

        $depotList = [];
        if (isset($stockData['DepotAvailability']) && count($stockData['DepotAvailability']) > 0) {
            if (isset($stockData['DepotAvailability']['GetProductAvailability_FromDepot'][0])) {
                $depotList = $stockData['DepotAvailability']['GetProductAvailability_FromDepot'] ?? [];
            } else {
                $depotList[] = $stockData['DepotAvailability']['GetProductAvailability_FromDepot'] ?? [];
            }
        }
        $depotList = array_values(array_filter($depotList));

        $buyOnline = '';
        $localStock = $stockData['LocalStock'] ?? 0;
        if (!empty($depotList)) {
            $buyOnline = isset($depotList[0]) ? $depotList[0]['AvailabilityBreakdown']['AvailableOnline'] : '';
        }

        $productStockAvailabilitySchedule = [];
        if (isset($stockData['AdditionalAvailability']) && count($stockData['AdditionalAvailability']) > 0) {
            if (isset($stockData['AdditionalAvailability']['ProductStockAvailabilitySchedule'][0])) {
                $productStockAvailabilitySchedule = $stockData['AdditionalAvailability']['ProductStockAvailabilitySchedule'] ?? [];
            } else {
                $productStockAvailabilitySchedule[] = $stockData['AdditionalAvailability']['ProductStockAvailabilitySchedule'] ?? [];
            }
        }
        $productStockAvailabilitySchedule = array_values(array_filter($productStockAvailabilitySchedule));

        $availabilityType = $isExistingPO = '';
        $psaQuantity = 0;
        foreach ($productStockAvailabilitySchedule as $productStockAvailability) {
            $availabilityType = $productStockAvailability['AvailabilityType'] ?? '';
            $isExistingPO = $productStockAvailability['IsExistingPO'] ?? '';
            $psaQuantity = (int) ($productStockAvailability['Quantity'] ?? 0);
        }

        return [
            'localStock' => $localStock,
            'availabilityType' => $availabilityType,
            'isExistingPO' => $isExistingPO,
            'psaQuantity' => $psaQuantity,
            'buyOnline' => $buyOnline,
            'depotList' => $depotList
        ];
    }

    /**
     * @param $manufacturer
     * @return bool
     */
    public function exVatPriceVisible($manufacturer): bool
    {
        return in_array($manufacturer, ['Ashvale', 'Bisley']);
    }

    /**
     * @param $attributeCode
     * @return bool
     */
    public function isSwatchAttributeAllowedOnPlp($attributeCode): bool
    {
        $swatchAttributes = $this->scopeConfig->getValue('actuate_config_product/general/attributes');
        $swatchAttributeCode = [];
        if (!empty($swatchAttributes)) {
            $swatchAttributeCode = explode(',', $swatchAttributes);
            $swatchAttributeCode = array_map('trim', $swatchAttributeCode);
        }

        return in_array($attributeCode, $swatchAttributeCode);
    }

    public function getLoadBeeApiKey()
    {
        return $this->scopeConfig->getValue('flix_media/general/loadbee_api_key', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getFlixMediaDistributorId()
    {
        return $this->scopeConfig->getValue('flix_media/general/distributor_id', ScopeInterface::SCOPE_STORE);
    }
}
