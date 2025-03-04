define(
    [
        'jquery',
        'uiComponent',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer'
    ],
    function (
        $,
        Component,
        registry,
        quote,
        customer
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    value: null,
                    code: 'cart_id',
                    mainListener: null,
                    exports: {
                        value: "${ $.provider }:conditions.${ $.code }"
                    }
                },

                observableProperties: [
                    'value'
                ],

                initialize: function () {
                    this._super()
                        .initEventObservers()
                        .initDefaultValue();

                    registry.async('index = ddConditionsListener')(function (listener) {
                        listener.addListener(this);
                        if (customer.isLoggedIn()) {
                            this.value.notifySubscribers(this.value());
                        }
                    }.bind(this));

                    return this;
                },

                /** @inheritdoc */
                initObservable: function () {
                    this._super()
                        .observe(this.observableProperties);

                    return this;
                },

                initDefaultValue: function () {
                    if (customer.isLoggedIn()) {
                        this.value(quote.getQuoteId());
                    }

                    return this;
                },

                initEventObservers: function () {
                    return this;
                }
            }
        );
    }
);
