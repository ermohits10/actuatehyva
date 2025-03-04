<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{

    protected $methodCode = 'v12finance';

    protected $method;

    protected $autifyDigitalHelper;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper
    ) {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    public function getConfig()
    {
        $outConfig = [
            'payment' => [
                $this->methodCode => $this->getFinanceOptions()
            ]
        ];
        return $outConfig;
    }

    public function getFinanceOptions()
    {
        $checkoutConfigArray = $this->autifyDigitalHelper->getCheckoutConfig();
        $enableSaleCategory = $checkoutConfigArray['sale_category_enable'];
    	$saleCategoryId = $checkoutConfigArray['sale_category_id'];
    	$skuFinanceEnable = $checkoutConfigArray['sku_enable_finance'];
    	$skuFinanceList = $checkoutConfigArray['sku_list'];
        $explodedSkuList = [];
        if(!empty($skuFinanceList)) {
            $explodedSkuList = explode(',', $skuFinanceList);    
        }
    	$priceBasedFinance = $checkoutConfigArray['price_based_finance'];
    	$isFinanceSkuExist = false;
    	$minPercentage = $checkoutConfigArray['min_percentage_value'];
    	$maxPercentage = $checkoutConfigArray['max_percentage_value'];
    	$minFinanceDeposit = $checkoutConfigArray['min_deposit'];
    	$maxFinanceDeposit = $checkoutConfigArray['max_deposit'];

    	$quote = $this->autifyDigitalHelper->getQuote();
    	$quoteItems = $quote->getAllVisibleItems();
        $couponCode = $quote->getCouponCode();

    	$productCategoryExist = false;

        $financeSkuArray = $salePriceProductArray = array();

        foreach ($quoteItems as $item) {
            $product = $item->getProduct();
            $productCategoryIds = $product->getCategoryIds();
            if(in_array($saleCategoryId, $productCategoryIds)){
                $productCategoryExist = true;
                break;
            }
            if(in_array($item->getSku(), $explodedSkuList)){
                $financeSkuArray[] = 1;
            }
            $salePriceProductArray[] = $this->autifyDigitalHelper->checkSalePriceProduct($product);
         }

        $orderTotal = $quote->getGrandTotal();
        if(in_array(1, $salePriceProductArray) && $checkoutConfigArray['sale_price_enable'] === '1') {
            $getAllServices = $this->autifyDigitalHelper->getSaleFinanceOptions();
            $defaultFinanceOption = $checkoutConfigArray['default_sale_finance_options'];
        } else if(!empty($couponCode) && $checkoutConfigArray['checkout_couponcode_enable'] === '1') {
            $getAllServices = $this->autifyDigitalHelper->getSaleFinanceOptions();
            $defaultFinanceOption = $checkoutConfigArray['default_sale_finance_options'];
        } else if( $enableSaleCategory === '1' && $productCategoryExist === true ){
            $getAllServices = $this->autifyDigitalHelper->getSaleFinanceOptions();
            $defaultFinanceOption = $checkoutConfigArray['default_sale_finance_options'];
    	} else if ( $priceBasedFinance === '1' && $this->autifyDigitalHelper->getPriceBasedFinanceOptions($orderTotal) ) {
            $getAllServices = $this->autifyDigitalHelper->getPriceBasedFinanceOptions($orderTotal);
            $defaultFinanceOption = $checkoutConfigArray['default_price_finance_options'];
        } else if ( $skuFinanceEnable === '1' && in_array(1, $financeSkuArray)) {
            $getAllServices = $this->autifyDigitalHelper->getSkuBasedFinanceOptions();
            $defaultFinanceOption = $checkoutConfigArray['default_sku_finance_options'];
        } else {
    	    $getAllServices = $this->autifyDigitalHelper->getNormalFinanceOptions();
    	    $defaultFinanceOption = $checkoutConfigArray['default_finance_option'];
        }

        $getPlaceOrderText = $checkoutConfigArray['checkout_page_payment_button'];
        $getBillingAddressText = $checkoutConfigArray['checkout_page_billing_text'];
        $checkoutMessage = $checkoutConfigArray['checkout_page_message_text'];
        $checkoutMessageTime = $checkoutConfigArray['checkout_page_message_time'];
        $checkoutFinanceError = $checkoutConfigArray['checkout_financeamount_text'];

    	return array(
    	    'default_finance_option' => $defaultFinanceOption,
    	    'finance_options' => $getAllServices,
    	    'min_finance_percentage' => $minPercentage,
    	    'max_finance_percentage' => $maxPercentage,
    	    'min_finance_deposit' => $minFinanceDeposit,
    	    'max_finance_deposit' => $maxFinanceDeposit,
    	    'get_place_order_text' => $getPlaceOrderText,
    	    'get_billing_address_text' => $getBillingAddressText,
    	    'currency_code' => $this->autifyDigitalHelper->getCurrentCurrencyCode(),
    	    'checkout_update_finance_message' => $checkoutMessage,
    	    'checkout_message_time' => $checkoutMessageTime,
    	    'finance_depositerror_message' => $checkoutFinanceError
    	);
    }

}
