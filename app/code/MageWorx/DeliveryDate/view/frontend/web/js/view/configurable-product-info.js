/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'underscore',
    'mage/translate',
    'jquery/ui'
], function ($, Component, _, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MageWorx_DeliveryDate/catalog/product/configurable/info',
            errorMessage: $t('Delivery is unavailable.'),
            selectOptionsMessageEnabled: false,
            selectOptionsMessage: $t('Please, select all required options to calculate the nearest delivery date.'),
            visible: true,
            displayErrorMessage: false
        },

        observableProperties: [
            'visible',
            'estimatedDeliveryTimeMessage',
            'cpid',
            'currentSuperAttributes',
            'childItems',
            'selectedItem',
            'errorMessage',
            'displayErrorMessage',
            'selectOptionsMessageEnabled',
            'selectOptionsMessage',
            'mainProduct',
            'deliveryDateMessage',
            'timezone',
            'locale',
        ],

        initialize: function () {
            this._super();

            return this;
        },

        initObservable: function () {
            this._super();
            this.observe(this.observableProperties);
            this.initSubscribers();

            return this;
        },

        initSubscribers: function () {

            // Set Initial visibility: when no option selected and "Select Option Message" disabled
            if (!this.selectedItem() && !this.selectOptionsMessageEnabled()) {
                this.visible(false);
            }

            if (!this.selectedItem()) {
                this.estimatedDeliveryTimeMessage(this.selectOptionsMessage());
            }

            // Show hide component
            this.selectedItem.subscribe(function (value) {
                if (_.isEmpty(value)) {
                    // No option selected
                    if (this.selectOptionsMessageEnabled()) {
                        // When "Select Options Message" enabled
                        this.visible(true);
                        this.estimatedDeliveryTimeMessage(this.selectOptionsMessage());
                    } else {
                        // When "Select Options Message" disabled
                        this.visible(false);
                    }
                } else {
                    // Option selected
                    this.visible(value.delivery_date_visible);
                    this.estimatedDeliveryTimeMessage(this.prepareEstimatedDeliveryTimeMessage(value));
                }
            }, this);

            this.currentSuperAttributes.subscribe(function (superAttributes) {
                var selectedItem = this.findSelectedItem(superAttributes);
                this.visible(true);
                this.selectedItem(selectedItem);
            }, this);
        },

        findSelectedItem: function (superAttributes) {
            if (_.isEmpty(this.childItems())) {
                return {};
            }

            var itemFound = false,
                selectedItem = {};

            _.each(this.childItems(), function (element, index, list) {
                if (itemFound) {
                    return;
                }

                var attributes = element.super_attributes,
                    found = 0;

                if (Object.keys(attributes).length !== Object.keys(superAttributes).length) {
                    return;
                }

                _.each(superAttributes, function (attr, idx) {
                    if (attributes[idx] && attributes[idx] == attr) {
                        found++;
                    }
                }, this);

                if (found !== 0 && found === Object.keys(attributes).length) {
                    itemFound = true;
                    selectedItem = element;
                }
            }, this);

            return selectedItem;
        },

        initConfigurationListener: function () {
            $('.swatch-opt, .swatch-opt-' + this.cpid()).click(function () {
                var selected_options = {};
                $('div.swatch-attribute').each(function (k, v) {
                    var attribute_id = $(v).data('attribute-id'),
                        option_selected = $(v).find('div.selected').data('option-id');

                    if (!attribute_id || !option_selected) {
                        return;
                    }

                    selected_options[attribute_id] = option_selected;
                });

                this.currentSuperAttributes(selected_options);
            }.bind(this));
        },

        prepareEstimatedDeliveryTimeMessage: function (productData) {
            let message = '',
                format = productData.format || this.deliveryDateMessage(),
                fromDay,
                toDay;

            if (productData.error && this.displayErrorMessage() && this.errorMessage()) {
                message = this.getErrorEDM(productData);
            } else if (!format || typeof format !== 'string') {
                return message;
            } else {
                fromDay = productData.from ?? 0;
                toDay = productData.to ?? 0;

                message = this.getEDMByFormat(fromDay, toDay, format);
            }

            return message;
        },

        getErrorEDM: function (data) {
            return data && data.errorMessage ? data.errorMessage : this.errorMessage();
        },

        /**
         *
         * @param {number} fromDay
         * @param {number} toDay
         * @param {!String} format
         */
        getEDMByFormat: function (fromDay, toDay, format) {
            fromDay = fromDay ?? 0;
            toDay = toDay ?? 0;

            let currentLocale = this.locale(),
                timezone = this.timezone(),
                dateFrom = new Date((new Date()).setDate((new Date()).getDate() + fromDay)),
                dateTo = new Date((new Date()).setDate((new Date()).getDate() + toDay)),
                fromCalendarDay = dateFrom.toLocaleDateString(currentLocale, {"day": 'numeric', "timeZone": timezone}),
                toCalendarDay = dateTo.toLocaleDateString(currentLocale, {"day": 'numeric', "timeZone": timezone}),
                fromCalendarMonth = dateFrom.toLocaleDateString(currentLocale, {"month": 'long', "timeZone": timezone}),
                toCalendarMonth = dateTo.toLocaleDateString(currentLocale, {"month": 'long', "timeZone": timezone}),
                message = '';

            message = format.replace('{{days_from_number}}', String(fromDay))
                .replace('{{days_to_number}}', String(toDay))
                .replace('{{days_from_calendar}}', fromCalendarDay)
                .replace('{{days_to_calendar}}', toCalendarDay)
                .replace('{{month_from}}', fromCalendarMonth)
                .replace('{{month_to}}', toCalendarMonth);

            return message;
        }
    });
});
