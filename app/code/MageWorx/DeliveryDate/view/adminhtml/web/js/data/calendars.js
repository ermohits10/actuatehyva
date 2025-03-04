define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        'jquery/validate'
    ],
    function ($, _, $tr) {
        'use strict';

        /* eslint-disable require-jsdoc, no-unused-vars */

        var CalendarList = [];

        function CalendarInfo() {
            this.id = null;
            this.name = null;
            this.checked = true;
            this.color = null;
            this.bgColor = null;
            this.borderColor = null;
        }

        function addCalendar(calendar) {
            CalendarList.push(calendar);
        }

        // Add calendars here
        return (function () {
            var calendar;

            if (!_.isUndefined(window.mageworx.delivery_date.config.delivery_options) &&
                window.mageworx.delivery_date.config.delivery_options.length > 0
            ) {
                var deliveryOptionsList = window.mageworx.delivery_date.config.delivery_options;
                for (var key in deliveryOptionsList) {
                    if (!deliveryOptionsList.hasOwnProperty(key)) {
                        continue;
                    }

                    var currentDeliveryOption = deliveryOptionsList[key],
                        holidaysCalendar,
                        nonWorkingDaysCalendar;

                    calendar = new CalendarInfo();
                    calendar.id = currentDeliveryOption.entity_id;
                    calendar.name = currentDeliveryOption.name;
                    calendar.color          = '#4286f4';
                    calendar.bgColor        = '#dae1ff';
                    calendar.dragBgColor    = '#4286f4';
                    calendar.borderColor    = '#4286f4';
                    addCalendar(calendar);

                    holidaysCalendar = new CalendarInfo();
                    holidaysCalendar.id = 'holiday';
                    holidaysCalendar.name = $tr('Holidays');
                    holidaysCalendar.color          = '#ffffff';
                    holidaysCalendar.bgColor        = '#ff7d7d';
                    holidaysCalendar.dragBgColor    = '#e6002b';
                    holidaysCalendar.borderColor    = '#e6002b';
                    addCalendar(holidaysCalendar);

                    nonWorkingDaysCalendar = new CalendarInfo();
                    nonWorkingDaysCalendar.id = 'non_working';
                    nonWorkingDaysCalendar.name = $tr('Non working days');
                    nonWorkingDaysCalendar.color          = '#ffffff';
                    nonWorkingDaysCalendar.bgColor        = '#88d679';
                    nonWorkingDaysCalendar.dragBgColor    = '#19af46';
                    nonWorkingDaysCalendar.borderColor    = '#19af46';
                    addCalendar(nonWorkingDaysCalendar);
                }
            }

            return CalendarList;
        })();
    }
);
