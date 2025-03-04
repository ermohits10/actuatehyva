/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry'
], function (quote, shippingRateRegistry) {
    'use strict';

    var timerId;

    return function () {
        // Prevent many saves on each change
        clearTimeout(timerId);
        timerId = setTimeout(function () {
            var address = quote.shippingAddress();
            if (shippingRateRegistry.get(address.getCacheKey())) {
                shippingRateRegistry.set(address.getCacheKey(), null);
            }

            quote.shippingAddress(address);
        }, 200);
    };
});
