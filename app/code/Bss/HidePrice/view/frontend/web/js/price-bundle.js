/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
], function ($) {
    'use strict';
    return function (widget) {

        $.widget('mage.priceBundle', widget, {
            _create: function createPriceBundle() {
                var form = this.element,
                    options = $(this.options.productBundleSelector, form),
                    priceBox = $(this.options.priceBoxSelector, form),
                    qty = $(this.options.qtyFieldSelector, form);

                if (priceBox.data('magePriceBox') &&
                    priceBox.priceBox('option') &&
                    priceBox.priceBox('option').priceConfig
                ) {
                    if (priceBox.priceBox('option').priceConfig.optionTemplate) {
                        this._setOption('optionTemplate', priceBox.priceBox('option').priceConfig.optionTemplate);
                    }
                    this._setOption('priceFormat', priceBox.priceBox('option').priceConfig.priceFormat);
                    priceBox.priceBox('setDefault', this.options.optionConfig.prices);
                }
                this._applyOptionNodeFix(options);

                options.on('change', this._onBundleOptionChanged.bind(this));
                qty.on('change', this._onQtyFieldChanged.bind(this));
                options.on('change', this.hidePriceAction.bind(this));
            },
            hidePriceAction: function onBundleOptionChanged(event) {
                var priceBox = $(this.options.priceBoxSelector, this.element);
                var hidePriceOptions = this.options.optionConfig.hidePrice;
                var hideCartOptions = this.options.optionConfig.hideCart;
                var formInput = this.element.serializeArray();
                var hidePriceBox = false;
                var hideCartBox = false;
                $.each(formInput, function( index1, value1 ) {
                    $.each(hidePriceOptions, function(index2, value2) {
                        if (value1['name'] == 'bundle_option[' + index2 + ']' && value1['value'] == value2) {
                            hidePriceBox = true;
                            return false;
                        }
                    });
                    $.each(hideCartOptions, function(index3, value3) {
                        if (value1['name'] == 'bundle_option[' + index3 + ']' && value1['value'] == value3) {
                            hideCartBox = true;
                            return false;
                        }
                    });
                    if (hidePriceBox === true && hideCartBox === true) {
                        return false;
                    }
                });
                if (hidePriceBox === true) {
                    priceBox.hide();
                } else {
                    priceBox.show();
                }
                if (hideCartBox === true) {
                    $('#product-addtocart-button').hide();
                } else {
                    $('#product-addtocart-button').show();
                }
            }
        });
        return $.mage.priceBundle;
    }
});