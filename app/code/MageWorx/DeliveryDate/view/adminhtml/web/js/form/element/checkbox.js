/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/single-checkbox',
    'underscore',
    'mage/translate'
], function (AbstractField, _, $t) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            checked: true,
            initialChecked: true,
            multiple: false,
            prefer: 'toggle', // 'radio' | 'checkbox' | 'toggle'
            default: "1"
        },

        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            if (_.isEmpty(this.valueMap)) {
                this.on('value', this.onUpdate.bind(this));
            } else {
                this.checked(this.value() === 1 || this.value() === "1" || this.value() === true ? true : false);
            }

            return this;
        },

        /**
         * @inheritdoc
         */
        initConfig: function (config) {
            if (typeof this.value === 'undefined' || this.value === null) {
                this.value = 1;
                this.initialValue = this.value;
            } else {
                this.initialValue = this.value;
            }

            this._super();
            return this;
        }
    });
});
