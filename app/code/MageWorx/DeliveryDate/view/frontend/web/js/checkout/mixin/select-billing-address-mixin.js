/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'mage/utils/wrapper',
    'underscore'
], function (wrapper, _) {
    'use strict';

    return function (selectBillingAddressAction) {

        return wrapper.wrap(selectBillingAddressAction, function (originalAction, billingAddress) {
            if (_.isEmpty(mwDeliveryDateConfig) || !mwDeliveryDateConfig.enabled) {
                return originalAction(billingAddress);
            }

            if (billingAddress === null) {
                return originalAction(billingAddress);
            }

            if (typeof billingAddress.street === 'undefined') {
                billingAddress.street = [];
            }

            var updatedAddress = _.extend({}, billingAddress);
            if (updatedAddress.customAttributes) {
                delete billingAddress.customAttributes.delivery_day;
                delete billingAddress.customAttributes.delivery_time;
                delete billingAddress.customAttributes.delivery_time_from;
                delete billingAddress.customAttributes.delivery_time_to;
                delete billingAddress.customAttributes.delivery_option_id;
                delete billingAddress.customAttributes.delivery_comment;
            }

            return originalAction(updatedAddress);
        });
    };
});
