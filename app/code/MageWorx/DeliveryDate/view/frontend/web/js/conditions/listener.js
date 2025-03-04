define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'underscore',
        'jquery/ui'
    ],
    function (ko, $, Component, _) {
        "use strict";

        /**
         * @TODO How to add and start listeners?
         */

        return Component.extend({
            defaults: {
                listeners: [],
                conditions: {},
                exports: {
                    conditions: "${ $.provider }:conditions"
                }
            },

            observableProperties: [
                "listeners",
                "conditions"
            ],

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                this._super();

                return this;
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe(this.observableProperties);

                return this;
            },

            addListener: function (listener) {
                listener.mainListener = this;
                this.listeners.push(listener);

                return this;
            }
        });
    }
)
