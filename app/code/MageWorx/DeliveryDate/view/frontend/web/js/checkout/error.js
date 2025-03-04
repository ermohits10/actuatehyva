define(
    [
        'ko',
        'jquery',
        'uiComponent'
    ],
    function (ko, $, Component) {
        "use strict";
        return Component.extend({
            defaults: {
                isVisible: false,
                requiredErrorMessage: $.mage.__('Delivery Date is required')
            },

            observableProperties: [
                'isVisible',
                'requiredErrorMessage'
            ],

            initObservable: function () {
                this._super();
                this.observe(this.observableProperties);

                this.isVisible.subscribe(function (visibility) {
                    if (visibility) {
                        $('#shipping-method-buttons-container button[type="submit"]').prop('disabled', true);
                    } else {
                        $('#shipping-method-buttons-container button[type="submit"]').prop('disabled', false);
                    }
                });

                return this;
            }
        });
    }
);
