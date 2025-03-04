<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Block\Index;

class CategoryViewFinance extends \Magento\Framework\View\Element\Template
{

    protected $autifyDigitalHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    public function getCategoryFinance($productConfigArray, $product)
    {
        if($product->isAvailable()) {
            $categoryIds = $product->getCategoryIds();
            $productFinalPrice = $product->getFinalPrice();

            $minOrderAmount = $productConfigArray['min_order_total'];
            $maxOrderAmount = $productConfigArray['max_order_total'];
            $minFinanceDeposit = $productConfigArray['min_deposit'];
            $maxFinanceDeposit = $productConfigArray['max_deposit'];

            $enableSaleCategory = $productConfigArray['sale_category_enable'];
            $saleCategoryId = $productConfigArray['sale_category_id'];

            $skuFinanceEnable = $productConfigArray['sku_enable_finance'];
            $skuFinanceList = explode(",", $productConfigArray['sku_list'] ?? "");

            $isFinanceSkuExist = false;
            if(is_array($skuFinanceList) && in_array($product->getSku(), $skuFinanceList)){
                $isFinanceSkuExist = true;
            }

            $productDisableCategory = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/finance_category_option');
            $productDisableCategoryIds = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/finance_category_list');
            $displayFinance = true;
            if($productDisableCategory) {
                if($productDisableCategoryIds) {
                    $productDisableCategoryIdsArray = explode(",", $productDisableCategoryIds ?? "");
                }
                foreach($categoryIds as $productCategoryId) {
                    if(in_array($productCategoryId, $productDisableCategoryIdsArray)) {
                        $displayFinance = false;
                        break;
                    }
                }
            }

            if(!$displayFinance) {
                return '';
            }

            $priceBasedFinance = $productConfigArray['price_based_finance'];
            if($productFinalPrice >= $minOrderAmount && $productFinalPrice <= $maxOrderAmount) {
                if( $this->autifyDigitalHelper->checkSalePriceProduct($product) === '1' && $productConfigArray['sale_price_enable'] === '1' ){
                    $defaultFinanceOption = $productConfigArray['default_sale_finance_options'];
                }elseif( $enableSaleCategory === '1' && in_array($saleCategoryId, $product->getCategoryIds()) ){
                    $defaultFinanceOption = $productConfigArray['default_sale_finance_options'];
                } else if ( $priceBasedFinance === '1' && $this->autifyDigitalHelper->getPriceBasedFinanceOptions($productFinalPrice) ) {
                    $defaultFinanceOption = $productConfigArray['default_price_finance_options'];
                } else if ( $skuFinanceEnable === '1' && $isFinanceSkuExist) {
                    $defaultFinanceOption = $productConfigArray['default_sku_finance_options'];
                } else {
                    $defaultFinanceOption = $productConfigArray['default_finance_option'];
                }
                $currencyCode = $this->autifyDigitalHelper->getCurrentCurrencyCode();

                $minPercentage = $productConfigArray['min_percentage_value'];
                $deposit = ($productFinalPrice * $minPercentage)/100;
                $explodedFinanceOption = explode('|', $defaultFinanceOption ?? "");
                $interest = 0;
                $month = 0;
                $calculationFactor = 0;

                if(is_array($explodedFinanceOption)) {
                    if(isset($explodedFinanceOption[3])) {
                        $interest = $explodedFinanceOption[3];
                    }
                    if(isset($explodedFinanceOption[4])) {
                        $month = $explodedFinanceOption[4];
                    }
                    if(isset($explodedFinanceOption[5])) {
                        $calculationFactor = $explodedFinanceOption[5];
                    }
                }

                $productExcludedPrice = $productFinalPrice - $deposit;

                $monthlyAmount = 0;

                if(isset($explodedFinanceOption[3])) {
                    if($interest > 0) {
                        $monthlyAmount = $calculationFactor * $productExcludedPrice;
                    } else {
                        $monthlyAmount = $productExcludedPrice/$month;
                    }
                    return 'From ' . $currencyCode . number_format($monthlyAmount, 2) . ' Per Month ' . $explodedFinanceOption[3] . '% APR';
                }
            }

            return '';
        }
    }
}
