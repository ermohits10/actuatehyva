/**
 * Copyright ©MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/textarea',
    'MageWorx_DeliveryDate/js/model/delivery-data-storage'
], function (
    Component,
    deliveryDateDataStorage
) {
    'use strict';

    return Component.extend({

        /**
         * @returns {*}
         */
        initialize: function () {
            this._super()
                .initSubscribers()
                .initDefaultValue()
                .initPreRenderFunctions();

            return this;
        },

        /**
         * Update value in the temp. storage
         *
         * @returns {*}
         */
        initSubscribers: function () {
            this.value.subscribe(function (value) {
                if (value !== undefined && value !== '') {
                    deliveryDateDataStorage.setComment(value);
                }
            }.bind(this));

            return this;
        },

        /**
         * Setting default value from storage
         *
         * @returns {*}
         */
        initDefaultValue: function () {
            let origValue = this.value(),
                storageValue = deliveryDateDataStorage.getComment();

            if (!origValue && storageValue) {
                this.value(storageValue);
            }

            return this;
        },

        /**
         * Regulate component visibility based on store settings.
         * Update label according store settings.
         *
         * @returns {*}
         */
        initPreRenderFunctions: function () {
            if (!mwDeliveryDateConfig.comment_field_visible) {
                this.visible(false);
            }

            if (mwDeliveryDateConfig.comment_field_label) {
                this.label = mwDeliveryDateConfig.comment_field_label;
            }

            return this;
        }
    });
});
