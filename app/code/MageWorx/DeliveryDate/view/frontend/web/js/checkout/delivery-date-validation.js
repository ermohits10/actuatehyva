/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'MageWorx_DeliveryDate/js/checkout/delivery-date-validator'
], function (Component, additionalValidators, deliveryDateValidator) {
    'use strict';

    additionalValidators.registerValidator(deliveryDateValidator);

    return Component.extend({});
});
