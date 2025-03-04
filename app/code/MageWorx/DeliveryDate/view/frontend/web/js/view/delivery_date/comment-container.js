define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'jquery/ui'
    ],
    function (ko, $, Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'MageWorx_DeliveryDate/container/comment',
                isVisible: true
            },

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
                    .observe('isVisible');

                return this;
            }
        });
    }
);
