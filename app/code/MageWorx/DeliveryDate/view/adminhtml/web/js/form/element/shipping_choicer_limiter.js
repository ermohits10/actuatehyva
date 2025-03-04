/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'underscore',
    'mage/translate'
], function (AbstractField, uiRegistry, _, $t) {
    'use strict';

    return AbstractField.extend({

        defaults: {
            "exports": {
                "selectShippingMethodsVisibility": "index = methods:visible"
            }
        },

        initObservable: function () {
            this._super();
            this.observe('selectShippingMethodsVisibility');

            this.value.subscribe(function (value) {
                var shippingMethodsSelectVisible = 0;
                if (value == 1) {
                    shippingMethodsSelectVisible = 1;
                } else {
                    shippingMethodsSelectVisible = 0;
                }
                this.selectShippingMethodsVisibility(shippingMethodsSelectVisible);
            }, this);

            return this;
        }
    });
});
