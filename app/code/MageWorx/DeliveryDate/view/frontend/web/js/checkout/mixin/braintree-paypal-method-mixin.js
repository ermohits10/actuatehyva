/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(['uiRegistry', 'Magento_Checkout/js/model/quote', 'underscore'], function (registry, quote, _) {
    'use strict';

    return function (origComponent) {
        return origComponent.extend({
            /**
             * Get shipping address
             * @returns {Object}
             */
            getShippingAddress: function () {
                var address = quote.shippingAddress(),
                    streetLine1 = '',
                    streetLine2 = '';

                if (!_.isUndefined(address.street)) {
                    if (!_.isUndefined(address.street[0])) {
                        streetLine1 = address.street[0];
                    }

                    if (!_.isUndefined(address.street[1])) {
                        streetLine2 = address.street[1];
                    }

                    if (!_.isUndefined(address.street[2])) {
                        streetLine2 += ' ' + address.street[2];
                    }
                }

                return {
                    recipientName: address.firstname + ' ' + address.lastname,
                    line1: streetLine1,
                    line2: streetLine2,
                    city: address.city,
                    countryCode: address.countryId,
                    postalCode: address.postcode,
                    state: address.region
                };
            },
        });
    };
});
