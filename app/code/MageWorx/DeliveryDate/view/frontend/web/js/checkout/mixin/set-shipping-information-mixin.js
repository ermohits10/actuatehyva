/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'uiRegistry',
    'underscore'
], function (
    $,
    wrapper,
    quote,
    checkoutDataResolver,
    registry,
    _
) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            if (_.isEmpty(mwDeliveryDateConfig) || !mwDeliveryDateConfig.enabled) {
                console.log('Mageworx Delivery Date is disabled. Proceed without it.');
                return originalAction();
            }

            let shippingAddress = quote.shippingAddress();

            if (!shippingAddress) {
                checkoutDataResolver.resolveShippingAddress();
                shippingAddress = quote.shippingAddress()
            }

            let deliveryDateSource = registry.get('deliveryDateProvider'),
                deliveryDateData = deliveryDateSource.delivery_date || {};

            if (shippingAddress['extensionAttributes'] === undefined) {
                shippingAddress['extensionAttributes'] = {};
            }

            shippingAddress['extensionAttributes']['delivery_comment'] = deliveryDateData['delivery_comment'];
            shippingAddress['extensionAttributes']['delivery_day'] = deliveryDateData['delivery_day'];
            shippingAddress['extensionAttributes']['delivery_option_id'] = deliveryDateData['delivery_option_id'];
            shippingAddress['extensionAttributes']['delivery_time'] = deliveryDateData['delivery_time'];

            // Validate custom attributes and
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            deliveryDateSource.set('params.invalid', false);
            deliveryDateSource.trigger('delivery_date.data.validate');

            if (deliveryDateSource.get('params.invalid')) {
                return {
                    done: function (any) {
                        // Plug
                    }
                };
            } else {
                return originalAction();
            }
        });
    };
});
