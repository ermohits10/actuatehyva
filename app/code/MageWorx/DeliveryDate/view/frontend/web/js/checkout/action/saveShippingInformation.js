/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/action/set-shipping-information',
    'MageWorx_DeliveryDate/js/checkout/action/reloadShippingMethods',
    'Magento_Checkout/js/model/quote',
    'jquery'
], function (setShippingInfoAction, reloadShippingMethods, quote, $) {
    'use strict';

    var timerId;

    return function () {
        // Prevent many saves on each change
        clearTimeout(timerId);
        timerId = setTimeout(function () {
            if (quote.shippingMethod()) {
                setShippingInfoAction().done(function (result) {
                    reloadShippingMethods();
                }).always(
                    function () {
                        // Make inputs available in case when spinner disables it and didn't turn it back on
                        $('[name="delivery_day"]').prop('disabled', false);
                        $('[name="delivery_time"]').prop('disabled', false);
                    }
                );
            }
        }, 300);
    };
});
