/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'mageUtils',
    'moment',
    'Magento_Ui/js/grid/columns/column',
    'mage/translate'
], function (utils, moment, Column, $t) {
    'use strict';

    function parseTime(value) {
        return !value ? '00' : (String(value).length === 1 ? '0' + value : value);
    }

    return Column.extend({
        defaults: {
            dateFormat: 'MMM d, YYYY',
            calendarConfig: []
        },

        /**
         * Overrides base method to normalize date format.
         *
         * @returns {DateColumn} Chainable.
         */
        initConfig: function () {
            this._super();

            this.dateFormat = utils.normalizeDate(this.dateFormat ? this.dateFormat : this.options.dateFormat);

            return this;
        },

        /**
         * Formats incoming date based on the 'dateFormat' property.
         *
         * @returns {String} Formatted date.
         */
        getLabel: function (value, format) {
            var date;

            if (this.storeLocale !== undefined) {
                moment.locale(this.storeLocale, utils.extend({}, this.calendarConfig));
            }
            date = moment(this._super());

            date = date.isValid() && value[this.index] ?
                date.format(format || this.dateFormat) :
                '';

            if (date &&
                (
                    value['delivery_hours_from'] ||
                    value['delivery_minutes_from'] ||
                    value['delivery_hours_to'] ||
                    value['delivery_minutes_to']
                )
            ) {
                if (value['delivery_hours_to'] != 0 || value['delivery_minutes_to'] != 0) {
                    date += ' ' + parseTime(value['delivery_hours_from']) + ':' + parseTime(value['delivery_minutes_from']) + ' - ' +
                        parseTime(value['delivery_hours_to']) + ':' + parseTime(value['delivery_minutes_to'])
                }
            }

            return date;
        }
    });
});
