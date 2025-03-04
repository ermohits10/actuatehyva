/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
var config = {
    config: {
        mixins: {
            // Replace
            'Magento_Checkout/js/action/set-shipping-information': {
                'MageWorx_DeliveryDate/js/checkout/mixin/set-shipping-information-mixin': true
            },
            // Save as bugfixes and compatibilities
            'Magento_Checkout/js/action/select-billing-address': {
                'MageWorx_DeliveryDate/js/checkout/mixin/select-billing-address-mixin': true
            },
            'Magestore_OneStepCheckout/js/action/set-shipping-information': {
                'MageWorx_DeliveryDate/js/checkout/mixin/set-shipping-information-mixin': true
            },
            'Aheadworks_OneStepCheckout/js/action/set-shipping-information': {
                'MageWorx_DeliveryDate/js/checkout/mixin/set-shipping-information-mixin': true
            },
            'PayPal_Braintree/js/view/payment/method-renderer/paypal': {
                'MageWorx_DeliveryDate/js/checkout/mixin/braintree-paypal-method-mixin': true
            },
            'Rokanthemes_OnePageCheckout/js/action/set-shipping-information': {
                'MageWorx_DeliveryDate/js/checkout/mixin/set-shipping-information-mixin': true
            }
        }
    },
    map: {
        '*': {
            deliveryDateWidget: 'MageWorx_DeliveryDate/js/widget/delivery_date',
            deliveryDateMultishippingWidget: 'MageWorx_DeliveryDate/js/widget/delivery_date_multishipping'
        },
        'MageWorx_DeliveryDate/js/delivery-options': {
            getDeliveryOptions: 'MageWorx_DeliveryDate/js/action/get-delivery-options-by-conditions'
        }
    }
};
