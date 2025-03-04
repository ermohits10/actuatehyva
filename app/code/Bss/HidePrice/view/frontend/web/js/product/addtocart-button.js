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
        'Magento_Catalog/js/product/uenc-processor'
    ],
    function (uencProcessor) {
        'use strict';

        return function (Component) {
            return Component.extend({
                getDataMageInit: function (row) {
                    if (row['add_to_cart_button']['hide_price']) {
                        return '{"redirectUrl": { "url" : "'  + uencProcessor(row['add_to_cart_button']['hide_price_url']) + '"}}';
                    }
                    return this._super(row);
                },
                getDataPost: function (row) {
                    if (row['add_to_cart_button']['hide_price']) {
                        return uencProcessor(row['add_to_cart_button']['hide_price_post']);
                    }
                    return this._super(row);
                },
                checkHidePrice: function (row) {
                    if (row['add_to_cart_button']['hide_price']) {
                        return true;
                    }
                    return false
                },
                checkHidePriceUrl: function (row) {
                    if (uencProcessor(row['add_to_cart_button']['hide_price_url']) && uencProcessor(row['add_to_cart_button']['hide_price_url']) !== 'false') {
                        return uencProcessor(row['add_to_cart_button']['hide_price_url']);
                    }
                    return false
                },
                getHidePriceWidgetText: function (row) {
                    if (row['add_to_cart_button']['hide_price']) {
                        return row['add_to_cart_button']['hide_price'];
                    }
                    return this.label;
                },
            });
        }
    }
);
