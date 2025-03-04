/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'mage/cookies',
        'slider-rtl',
        'jquery/ui',
        'jquery-ui-slider'
    ],
    function ($, Component, url, quote, priceUtils, sliderRtl) {
        'use strict';
        var minimumFinancePercentage = window.checkoutConfig.payment.v12finance.min_finance_percentage;
        var maximumFinancePercentage = window.checkoutConfig.payment.v12finance.max_finance_percentage;
        var checkoutErrorTime = window.checkoutConfig.payment.v12finance.checkout_message_time;
        var currencyCode = window.checkoutConfig.payment.v12finance.currency_code;
        var minFinanceDeposit = window.checkoutConfig.payment.v12finance.min_finance_deposit;
        var maxFinanceDeposit = window.checkoutConfig.payment.v12finance.max_finance_deposit;
        var depositErrorMessage = window.checkoutConfig.payment.v12finance.finance_depositerror_message;
        var defaultFinanceOption = window.checkoutConfig.payment.v12finance.default_finance_option;
        var callRangeSlider = '';

        quote.paymentMethod.subscribe(function(method){
            checkV12FinanceVisibility(method);
        }, null, 'change');

        function checkV12FinanceVisibility(method) {
            if(method != 'v12finance') {
                $(document).find(".payment-methods button.action.primary.checkout.amasty").attr('disabled', false);
            }
        }

        var formkey = $.mage.cookies.get('form_key');
        var selectedFinance, selectedDeposit;
        var totals = quote.totals();
        if(totals['base_grand_total'] < totals['grand_total']) {
            var grandTotal = totals['grand_total'];
        } else {
            var grandTotal = totals['base_grand_total'];
        }
        var shippingAmount = totals['shipping_amount'];

        var minFinanceAmountTotal = parseFloat((minimumFinancePercentage*grandTotal)/100);
        var maxFinanceAmountTotal = parseFloat((maximumFinancePercentage*grandTotal)/100);

        var minFinanceAmount = Math.round(roundFinanceNum(minFinanceAmountTotal, 1));
        var maxFinanceAmount = Math.round(roundFinanceNum(maxFinanceAmountTotal, -0.2));

        quote.totals.subscribe(function(){
            reinitializeCalculator();
        }, null, 'change');

        function roundFinanceNum(val, multiplesOf) {
            var s = 1 / multiplesOf;
            var res = Math.ceil(val*s)/s;
            res = res < val ? res + multiplesOf: res;
            var afterZero = multiplesOf.toString().split(".")[1];
            return parseFloat(res.toFixed(afterZero ? afterZero.length : 0));
        }

        setInterval(function(){ enableBillingAddress() }, 1000);
        function enableBillingAddress() {
            var currentShippingMethod = quote.shippingMethod();
            var currentShippingMethodCode = '';
            if(currentShippingMethod) {
                currentShippingMethodCode = quote.shippingMethod()['carrier_code'];
            }
            if(currentShippingMethodCode && currentShippingMethodCode == 'instore') {
                $(document).find('.autify-v12-finance .payment-method-billing-address').show();
            } else {
                $(document).find('.autify-v12-finance .payment-method-billing-address').hide();
            }
        }

        function reinitializeCalculator() {
            var selectedFinance = $('#v12-finance-options').val();

            if(selectedFinance == null) {
                selectedFinance = $(document).find('#v12-finance-options').find("option:first-child").val();
                $(document).find('#v12-finance-options').val(selectedFinance);
                $(document).find('#v12-finance-deposit-error').html('Please check the finance option.');
                $(document).find('#v12-finance-deposit-error').show();
                return;
            }

            var selectedDeposit = $(document).find('.deposit-text-area .deposit_amount').attr('data-price');
            var totals = quote.totals();
            var totalsValue = (totals ? totals : quote)['grand_total'];

            if((totals ? totals : quote)['base_grand_total'] < (totals ? totals : quote)['grand_total']) {
                var totalsValue = (totals ? totals : quote)['grand_total'];
            } else {
                var totalsValue = (totals ? totals : quote)['base_grand_total'];
            }

            var minFinanceAmountTotal = parseFloat((minimumFinancePercentage*totalsValue)/100);
            var maxFinanceAmountTotal = parseFloat((maximumFinancePercentage*totalsValue)/100);

            var minFinanceAmount = Math.round(roundFinanceNum(minFinanceAmountTotal, 1));
            var maxFinanceAmount = Math.round(roundFinanceNum(maxFinanceAmountTotal, -0.2));

            $(document).find(".repayment-text .depositamount_small").text(priceUtils.formatPrice(minFinanceAmount));
            $(document).find(".repayment-text .depositamount_smallmax").text(priceUtils.formatPrice(maxFinanceAmount));

            if(selectedDeposit > maxFinanceAmount) {
                selectedDeposit = maxFinanceAmount;
            }

            if(selectedDeposit < minFinanceAmount) {
                selectedDeposit = minFinanceAmount;
            }

            reinitializeDeposit(minFinanceAmount, maxFinanceAmount, selectedDeposit);

            var monthlyContract = $('#v12-finance-options').find(':selected').data('month');
            var interest = $('#v12-finance-options').find(':selected').data('interest');
            var financeName = $('#v12-finance-options').find(':selected').data('finance-name');
            var calculationFactor = $('#v12-finance-options').find(':selected').data('cf');
            var minLoanFinanceAtV12 = $('#v12-finance-options').find(':selected').data('min-loan');
            var maxLoanFinanceAtV12 = $('#v12-finance-options').find(':selected').data('max-loan');

            var excludeDeposit = parseFloat(totalsValue)-parseFloat(selectedDeposit);
            if(interest > 0) {
                var monthlyAmount = excludeDeposit * calculationFactor;
            } else {
                var monthlyAmount = excludeDeposit/monthlyContract;
            }
            var monthlyAmount = Number(monthlyAmount).toFixed(4);
            var interestAmount = (monthlyAmount*monthlyContract)-excludeDeposit;
            var annualinterestrate = ((interestAmount/excludeDeposit)/(monthlyContract/12)*100);

            if(interest > 0) {
                var totalPayment = ((monthlyAmount*monthlyContract)+Number(selectedDeposit));
                var annualinterestrate = ((interestAmount/excludeDeposit)/(monthlyContract/12)*100);
                var financeAmountCheck = (monthlyAmount*monthlyContract);
            }else{
                var totalPayment = excludeDeposit+Number(selectedDeposit);
                var financeAmountCheck = excludeDeposit;
                var annualinterestrate = 0.00;
            }
            var totalDeposit = Math.round(selectedDeposit);

            $('.v12finance-summary .amount-deposit-price .value').text(currencyCode + Number(totalDeposit).toFixed(2));
            $('.v12finance-summary .amount-loan-price .value').text(currencyCode + Number(excludeDeposit).toFixed(2));
            $('.v12finance-summary .monthly-amount .value').text(currencyCode + Number(monthlyAmount).toFixed(2));
            $('.v12finance-summary .monthly-contract .value').text(monthlyContract + ' months');
            $('.v12finance-summary .amount-interest .value').text(interest.toFixed(2) + '%');
            $('.v12finance-summary .annual-rate-of-interest .value').text(Math.abs(annualinterestrate).toFixed(2) + '%');
            $('.v12finance-summary .total-amount-payable .value').text(currencyCode + Number(totalPayment).toFixed(2));
            $('.v12finance-summary .total-amount-payable-now .value').text(currencyCode + Number(totalDeposit).toFixed(2));
            $('.v12finance-summary .total-amount-payable .value').attr('total_price', totalPayment.toFixed(2));

            var totalPaymentDepositCheck = excludeDeposit;
            if(financeAmountCheck < minLoanFinanceAtV12 || financeAmountCheck > maxLoanFinanceAtV12) {
                $(document).find('.autify-v12-finance .error-message-v12').show();
                $(document).find('.autify-v12-finance .error-message-v12 .minloan-v12').text(minLoanFinanceAtV12.toFixed(2));
                $(document).find('.autify-v12-finance .error-message-v12 .maxloan-v12').text(maxLoanFinanceAtV12.toFixed(2));
                $(document).find("#v12-finance-placeorder-click button").attr('disabled', true);
                $(document).find(".payment-methods button.action.primary.checkout.amasty").attr('disabled', true);
            } else {
                $(document).find('.autify-v12-finance .error-message-v12').hide();
                if(totalPaymentDepositCheck >= minFinanceDeposit && totalPaymentDepositCheck <= maxFinanceDeposit) {
                    $(document).find(".autify-v12-finance .messages").html("");
                    $(document).find("#v12-finance-placeorder-click button").attr('disabled', false);
                    $(document).find(".payment-methods button.action.primary.checkout.amasty").attr('disabled', false);
                }else{
                    var errorMessage = '<div class="message-error error message"><div>' + depositErrorMessage + '</div></div>';
                    $(document).find(".autify-v12-finance .messages").html(errorMessage);
                    $(document).find(".autify-v12-finance .messages").show();
                    $(document).find("#v12-finance-placeorder-click button").attr('disabled', true);
                    $(document).find(".payment-methods button.action.primary.checkout.amasty").attr('disabled', true);
                }
            }

        }

        function reinitializeDeposit(minRangeValue, maxRangeValue, sliderValue) {
            $('.deposit-text-area .deposit_amount').val(Number(sliderValue).toFixed(0));
            $('.deposit-text-area .deposit_amount').attr("data-price", sliderValue);

            var hs = $('#slider-finance').slider();
            hs.slider('option',{min: minRangeValue, max: maxRangeValue});
            hs.slider('value', sliderValue);
            hs.trigger('slide',{ ui: $('.ui-slider-handle', hs), value: sliderValue });
        }

        function callSlider(minRangeValue, maxRangeValue, sliderValue) {

            if($(document).find('#slider-finance a').length > 0) {
                clearInterval(callRangeSlider);
            }else{
                $(document).find('#slider-finance').slider({
                    min: minRangeValue,
                    max: maxRangeValue,
                    value: sliderValue,
                    isRTL: false,
                    slide: function(e, ui){
                        reinitializeDeposit(minRangeValue, maxRangeValue, ui.value);
                        reinitializeCalculator();
                    },
                    change: function(e, ui){
                        reinitializeDeposit(minRangeValue, maxRangeValue, ui.value);
                        reinitializeCalculator();
                    }
                });
            }
        }

        initV12Finance();
        $(document).ready(initV12Finance);
        function initV12Finance(){
            $("body").on("change", "#v12-finance-options, #deposit_amount", function () {
                selectedFinance = $('#v12-finance-options').val();
                selectedDeposit = $('#deposit_amount').val();
                if(selectedFinance == '' || selectedDeposit == '' || selectedDeposit < minimumFinancePercentage || selectedDeposit > maximumFinancePercentage){
                    $(document).find("#v12-finance-placeorder-click button").attr('disabled', true);
                    $(document).find(".payment-methods button.action.primary.checkout.amasty").attr('disabled', true);
                }else {
                    reinitializeCalculator();
                }
            });
        }

        $("body").on("keyup", ".deposit-text-area .deposit_amount", function () {
            var currentValue = $(this).val();
            if(currentValue >= minFinanceAmount && currentValue <= maxFinanceAmount){
                reinitializeDeposit(minFinanceAmount, maxFinanceAmount, currentValue);
                reinitializeCalculator();
                $(document).find(".deposit .repayment-text").removeClass('blinker');
            } else {
                if(currentValue != '') {
                    $(document).find(".deposit .repayment-text").removeClass('blinker');
                    setTimeout(function () {
                        $(document).find(".deposit .repayment-text").removeClass('blinker');
                    }, 5000);
                }
            }
        });

        $("body").on("click", ".deposit-text-area #minus-icon", function () {
            var depositValue = parseInt($(document).find('.deposit-text-area .deposit_amount').attr("data-price"));
            if (depositValue <= minFinanceAmount) return;
            if(depositValue < 99) {
                depositValue--;
            }else if(depositValue >= 99 && depositValue <= 999) {
                depositValue -= 10;
                var fillCheck = depositValue - minFinanceAmount;
                if(fillCheck < 0){
                    depositValue += 10;
                }
            }else{
                depositValue -= 100;
                var fillCheck = depositValue - minFinanceAmount;
                if(fillCheck < 0){
                    depositValue += 100;
                }
            }
            reinitializeDeposit(minFinanceAmount, maxFinanceAmount, depositValue);
            reinitializeCalculator();
        });

        $("body").on("click", ".deposit-text-area #plus-icon", function () {
            var depositValue = parseInt($(document).find('.deposit-text-area .deposit_amount').attr("data-price"));
            if (depositValue >= maxFinanceAmount) return;
            if(depositValue < 99) {
                depositValue++;
            }else if(depositValue >= 99 && depositValue <= 999) {
                depositValue += 10;
                var fillPlusCheck = maxFinanceAmount - depositValue;

                if(fillPlusCheck < 0){
                    depositValue -= 10;
                }
            }else{
                depositValue += 100;
                var fillPlusCheck = maxFinanceAmount - depositValue;
                if(fillPlusCheck < 0){
                    depositValue -= 100;
                }
            }
            reinitializeDeposit(minFinanceAmount, maxFinanceAmount, depositValue);
            reinitializeCalculator();
        });

        return Component.extend({
            defaults: {
                template: 'AutifyDigital_V12Finance/payment/v12finance'
            },
            financeLogo: financeLogo,

            initialize: function (data, event) {
                $(document).find("#v12-finance-placeorder-click button").attr('disabled', true);
                $(document).find(".payment-methods button.action.primary.checkout.amasty").attr('disabled', true);
                this._super();
                var callRangeSlider = setInterval(function(){
                    callSlider(minFinanceAmount, maxFinanceAmount, minFinanceAmount)
                }, 1000);
                $('body').trigger('processStart');
                $(".currency-symbol").text(this.getCurrencyCode());
                $(document).find(".repayment-text .min-percentage").text(this.getMinimumFinancePercentage());

                if ($('#v12-finance-options option').length === 0) {
                    $(document).find("#v12-finance-placeorder-click button").attr('disabled', true);
                    $(document).find(".payment-methods button.action.primary.checkout.amasty").attr('disabled', true);
                    $("#v12-finance-deposit-error").html('Finance is available on orders between &pound;250 and &pound;15,000.');
                }

                if (document.cookie.match(new RegExp('(^| )finance=([^;]+)')) !== null
                    && document.cookie.match(new RegExp('(^| )finance=([^;]+)')) !== undefined) {
                    var financeCookie = document.cookie.match(new RegExp('(^| )finance=([^;]+)'))[2] || undefined;
                    if(typeof financeCookie === 'undefined') {
                        var financeJsonDecoded = "";
                    } else {
                        var financeJsonDecoded = $.parseJSON(financeCookie);
                    }
                } else {
                    var financeJsonDecoded = "";
                }


                var depositCookie = financeOptionCookie = '';
                if(financeJsonDecoded){
                    var depositCookie = parseFloat(financeJsonDecoded['deposit']);
                    var financeOptionCookie = financeJsonDecoded['finance_option'];
                }

                if(defaultFinanceOption == null){
                    defaultFinanceOption = $(document).find('#v12-finance-options').find("option:first-child").val();
                    $(document).find('#v12-finance-options').val(defaultFinanceOption);
                }

                var message = '<div class="message-success success message"><div>' + this.getUpdatedFinanceMessage() + '</div></div>';
                var messageDisplay = [];
                if(depositCookie){
                    setTimeout(function(){
                        if(minFinanceAmount > depositCookie) {
                            reinitializeDeposit(minFinanceAmount, maxFinanceAmount, minFinanceAmount);
                            messageDisplay.push(1);
                            $(document).find(".repayment-text .depositamount_small").text(priceUtils.formatPrice(minFinanceAmount));
                            $(document).find(".repayment-text .depositamount_smallmax").text(priceUtils.formatPrice(maxFinanceAmount));
                        }else if(maxFinanceAmount < depositCookie){
                            reinitializeDeposit(minFinanceAmount, maxFinanceAmount, minFinanceAmount);
                            messageDisplay.push(1);
                            $(document).find(".repayment-text .depositamount_small").text(priceUtils.formatPrice(minFinanceAmount));
                            $(document).find(".repayment-text .depositamount_smallmax").text(priceUtils.formatPrice(maxFinanceAmount));
                        }else{
                            reinitializeDeposit(minFinanceAmount, maxFinanceAmount, depositCookie);
                            $(document).find(".repayment-text .depositamount_small").text(priceUtils.formatPrice(minFinanceAmount));
                            $(document).find(".repayment-text .depositamount_smallmax").text(priceUtils.formatPrice(maxFinanceAmount));
                        }
                        if(!quote.paymentMethod._latestValue) {
                            $(document).find('.v12finance-payment-method').trigger('click');
                        }
                    }, 3000);
                }else{
                    setTimeout(function(){
                        reinitializeDeposit(minFinanceAmount, maxFinanceAmount, minFinanceAmount);
                        messageDisplay.push(1);
                        $(document).find(".repayment-text .depositamount_small").text(priceUtils.formatPrice(minFinanceAmount));
                        $(document).find(".repayment-text .depositamount_smallmax").text(priceUtils.formatPrice(maxFinanceAmount));
                    }, 3000);
                }

                if(financeOptionCookie != '') {
                    setTimeout(function(){
                        if($("#v12-finance-options option[value='"+financeOptionCookie+"']").length <= 0) {
                            messageDisplay.push(1);
                            $(document).find('#v12-finance-options').val(defaultFinanceOption).prop('selected', true).trigger('change');
                        }else{
                            $(document).find('#v12-finance-options').val(financeOptionCookie).trigger('change');
                        }
                    }, 3000);
                }else{
                    setTimeout(function(){
                        messageDisplay.push(1);
                        $(document).find('#v12-finance-options').val(defaultFinanceOption).prop('selected', true).trigger('change');
                    }, 3000);
                }
                setTimeout(function(){
                    if(jQuery.inArray(1, messageDisplay) >= 0) {
                        var checkoutMessageTime = parseInt(checkoutErrorTime)*1000;
                        $(document).find(".autify-v12-finance .messages").append(message).delay(checkoutMessageTime).fadeOut(300);
                    }
                }, 3000);

                setTimeout(function(){
                    $('body').trigger('processStop');
                }, 3000);
            },
            getPlaceOrderText: function(){
                return window.checkoutConfig.payment.v12finance.get_place_order_text;
            },
            getBillingAddressText: function(){
                return window.checkoutConfig.payment.v12finance.get_billing_address_text;
            },
            getCurrencyCode: function(){
                return currencyCode;
            },
            getMinimumFinancePercentage: function(){
                return minimumFinancePercentage;
            },
            getMaximumFinancePercentage: function(){
                return maximumFinancePercentage;
            },
            getMinimumFinanceDeposit: function(){
                return minFinanceDeposit;
            },
            getMaximumFinanceDeposit: function(){
                return maxFinanceDeposit;
            },
            getUpdatedFinanceMessage: function() {
                return window.checkoutConfig.payment.v12finance.checkout_update_finance_message;
            },
            getCheckoutErrorMessage: function(){
                return depositErrorMessage;
            },
            getCheckoutErrorMessageTime: function(){
                return checkoutErrorTime;
            },
            getFinanceOptions: function(){
                return window.checkoutConfig.payment.v12finance.finance_options;
            },
            getDefaultFinanceOptions: function(){
                return defaultFinanceOption;
            },
            afterPlaceOrder: function (data, event) {
                var financeId = $('#v12-finance-options').val();
                var depositPrice = parseInt($(document).find('.deposit-text-area .deposit_amount').attr("data-price"));
                var totalAmountPayable = $('.total-amount-payable .value').attr('total_price');
                $.cookie("finance", null, { path: '/' });
                this.redirectAfterPlaceOrder = false;
                var parameters = '?id='+financeId+'&deposit='+depositPrice+'&totalAmountPayable='+totalAmountPayable;
                window.location.replace(url.build('v12finance/index/callv12application'+parameters));
            },
        });

    }
);
