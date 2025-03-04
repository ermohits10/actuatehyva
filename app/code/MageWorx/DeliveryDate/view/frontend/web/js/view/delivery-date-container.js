define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'loader',
        'jquery/ui'
    ],
    function (ko, $, Component, loader) {
        "use strict";
        return Component.extend({
            defaults: {
                isVisible: false,
                isLoading: false,
                imports: {
                    isVisible: "${ $.deliveryOptionsStorage }:optionsLength",
                    isLoading: "${ $.deliveryOptionsStorage }:isLoading"
                }
            },

            observableProperties: [
                'isLoading',
                'isVisible'
            ],

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                this._super()
                    .initSubscribers();

                return this;
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe(this.observableProperties);

                return this;
            },

            initSubscribers: function () {

            }
        });
    }
);
