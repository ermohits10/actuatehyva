define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'getDeliveryOptions',
        'underscore',
        'jquery/ui'
    ],
    function (ko, $, Component, getDeliveryOptions, _) {
        "use strict";

        let timerId;

        return Component.extend({
            defaults: {
                options: {},
                optionsLength: 0,
                deliveryOptionId: 0,
                deliveryOptionName: 'None',
                deliveryOptionObj: {},
                isLoading: false,
                imports: {
                    conditions: "${ $.provider }:conditions"
                },
                exports: {
                    deliveryOptionId: "${ $.provider }:delivery_date.delivery_option_id"
                }
            },

            observableProperties: [
                "options",
                "optionsLength",
                "deliveryOptionId",
                "deliveryOptionName",
                "deliveryOptionObj",
                "conditions",
                "isLoading"
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
                let self = this;

                this.conditions.subscribe(function (conds) {
                    clearTimeout(timerId);
                    timerId = setTimeout(function () {
                        self.isLoading(true);
                        getDeliveryOptions.getDeliveryOptions(conds)
                            .done(function (response) {
                                let deliveryOption = response.pop(),
                                    options = deliveryOption ? deliveryOption['day_limits'] : [],
                                    deliveryOptionId = deliveryOption ? deliveryOption['entity_id'] : 0,
                                    deliveryOptionName = deliveryOption ? deliveryOption['name'] : 'Delivery Option is not available'

                                self.options(options);
                                self.optionsLength(options.length);
                                self.deliveryOptionId(deliveryOptionId);
                                self.deliveryOptionName(deliveryOptionName);
                                self.deliveryOptionObj(deliveryOption);
                            })
                            .always(function () {
                                self.isLoading(false);
                            });
                    }, 1000); // prevents multiple queries when a customer fills in an address
                }, this);
            }
        });
    }
)
