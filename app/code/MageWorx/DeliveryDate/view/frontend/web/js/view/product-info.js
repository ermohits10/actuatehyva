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
            template: 'MageWorx_DeliveryDate/catalog/product/info',
            errorMessage: $t('Delivery is unavailable.'),
            visible: true,
            displayErrorMessage: false
        },

        observableProperties: [
            'visible',
            'estimatedDeliveryTimeMessage',
            'errorMessage',
            'mainProduct',
            'displayErrorMessage',
            'deliveryDateMessage',
            'timezone',
            'locale'
        ],

        initialize: function () {
            this._super();

            return this;
        },

        initObservable: function () {
            this._super();
            this.observe(this.observableProperties);
            this.estimatedDeliveryTimeMessage(this.prepareEstimatedDeliveryTimeMessage(this.mainProduct() || {}));

            return this;
        },

        prepareEstimatedDeliveryTimeMessage: function (productData) {
            let message = '',
                format = productData.format || this.deliveryDateMessage(),
                fromDay,
                toDay;

            if (this.displayErrorMessage() && this.errorMessage()) {
                message = this.getErrorEDM();
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
