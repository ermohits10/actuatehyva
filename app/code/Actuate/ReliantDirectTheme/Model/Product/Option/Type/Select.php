<?php

namespace Actuate\ReliantDirectTheme\Model\Product\Option\Type;

use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Stdlib\StringUtils;

class Select extends \Magento\Catalog\Model\Product\Option\Type\Select
{
    private Data $pricingHelper;

    /**
     * @param Session $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StringUtils $string
     * @param Escaper $escaper
     * @param Data $pricingHelper
     * @param array $data
     * @param array $singleSelectionTypes
     * @param CalculateCustomOptionCatalogRule|null $calculateCustomOptionCatalogRule
     */
    public function __construct(
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        StringUtils $string,
        Escaper $escaper,
        Data $pricingHelper,
        array $data = [],
        array $singleSelectionTypes = [],
        CalculateCustomOptionCatalogRule $calculateCustomOptionCatalogRule = null
    ) {
        parent::__construct(
            $checkoutSession,
            $scopeConfig,
            $string,
            $escaper,
            $data,
            $singleSelectionTypes,
            $calculateCustomOptionCatalogRule
        );
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Return formatted option value for quote option
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     * @throws LocalizedException
     */
    public function getFormattedOptionValue($optionValue)
    {
        if ($this->_formattedOptionValue === null) {
            $this->_formattedOptionValue = $this->_escaper->escapeHtml($this->getEditableOptionValue($optionValue), ['ul', 'li']);
        }
        return $this->_formattedOptionValue;
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     * @throws LocalizedException
     */
    public function getEditableOptionValue($optionValue)
    {
        $option = $this->getOption();
        if (!$this->_isSingleSelection()) {
            $result = '<ul>';
            foreach (explode(',', (string)$optionValue) as $_value) {
                $_result = $option->getValueById($_value);
                if ($_result) {
                    $result .= '<li>' . $_result->getTitle() . ' - ' . $this->pricingHelper->currency($_result->getPrice(), true, false) . '</li>';
                } else {
                    if ($this->getListener()) {
                        $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                        $result = '';
                        break;
                    }
                }
            }
            $result .= '</ul>';
        } elseif ($this->_isSingleSelection()) {
            $_result = $option->getValueById($optionValue);
            if ($_result) {
                $result = $_result->getTitle();
            } else {
                if ($this->getListener()) {
                    $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                }
                $result = '';
            }
        } else {
            $result = $optionValue;
        }
        return $result;
    }
}
