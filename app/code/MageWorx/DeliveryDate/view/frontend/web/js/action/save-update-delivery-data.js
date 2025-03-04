define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'MageWorx_DeliveryDate/js/checkout/action/saveShippingInformation',
        'MageWorx_DeliveryDate/js/checkout/action/saveDeliveryDateInformation',
        'underscore',
        'jquery/ui'
    ],
    function (ko, $, Component, saveShippingInformation, saveDeliveryDateInformation, _) {
        "use strict";

        return Component.extend({
            defaults: {
                extraChargeOldValue: '',
                imports: {
                    deliveryDateExtraChargeHasBeenChanged: "${ $.provider }:extraCharge",
                    deliveryDate: "${ $.provider }:delivery_date",
                    shippingMethod: "${ $.provider }:conditions.shippingMethod"
                }
            },

            observableProperties: [
                "deliveryDateExtraChargeHasBeenChanged",
                "extraChargeOldValue",
                "deliveryDate",
                "shippingMethod"
            ],

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                this._super();
                this.initSubscribers();

                return this;
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe(this.observableProperties);

                return this;
            },

            initSubscribers: function () {
                // Save old extra charge value
                this.deliveryDateExtraChargeHasBeenChanged.subscribe(function(oldValue) {
                    if (oldValue === undefined || oldValue === null) {
                        this.extraChargeOldValue('');
                    } else {
                        this.extraChargeOldValue(oldValue);
                    }
                }, this, "beforeChange");

                // Update shipping method price on extra charge apply
                this.deliveryDateExtraChargeHasBeenChanged.subscribe(function (value) {
                    if (String(this.extraChargeOldValue()) !== String(value)) {
                        saveShippingInformation();
                    }
                }, this);

                // Save information about selected delivery date
                this.deliveryDate.subscribe(function (value) {
                    saveDeliveryDateInformation();
                }, this);

                // Update shipping method price in case method has been changed and extra charge exists
                this.shippingMethod.subscribe(function (value) {
                    if (this.deliveryDateExtraChargeHasBeenChanged() !== '') {
                        saveShippingInformation();
                    }
                }, this);
            }
        });
    }
)
