define(
    [
        'jquery',
        'uiComponent',
        'uiRegistry',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        $,
        Component,
        registry,
        quote
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    value: '',
                    code: 'shippingMethod',
                    mainListener: null,
                    exports: {
                        value: "${ $.provider }:conditions.${ $.code }"
                    }
                },

                observableProperties: [
                    'value'
                ],

                initialize: function () {
                    this._super();

                    this.initEventObservers();

                    registry.async('index = ddConditionsListener')(function (listener) {
                        listener.addListener(this);
                        this.value.notifySubscribers(this.value());
                    }.bind(this));

                    return this;
                },

                /** @inheritdoc */
                initObservable: function () {
                    this._super()
                        .observe(this.observableProperties);

                    return this;
                },

                initEventObservers: function () {
                    quote.shippingMethod.subscribe(function (shippingMethod) {
                        let shippingMethodCode = null;

                        if (shippingMethod) {
                            shippingMethodCode = shippingMethod['carrier_code'] + '_' + shippingMethod['method_code'];
                        }

                        // Update value anyway
                        this.value(shippingMethodCode);
                    }.bind(this));
                }
            }
        );
    }
);
