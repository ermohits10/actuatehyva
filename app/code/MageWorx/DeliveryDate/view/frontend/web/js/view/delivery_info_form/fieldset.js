define(
    [
        'Magento_Ui/js/form/components/fieldset',
        'underscore'
    ],
    function (Component, _) {
        'use strict';

        return Component.extend({
            defaults: {
                visible: false
            },

            initObservable: function () {
                this._super();

                var self = this;

                this.visible.subscribe(function (value) {
                    if (_.isArray(value)) {
                        value = Boolean(value.length);
                        return self.visible(value);
                    }
                });

                return this;
            }
        });
    }
);