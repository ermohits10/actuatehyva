"use strict";
define(
    [
        'ko',
        'Magento_Ui/js/form/element/date',
        'MageWorx_DeliveryDate/js/deliveryDayUtils',
        'uiRegistry',
        'underscore',
        'jquery',
        'mage/translate',
        'moment',
        'moment-timezone-with-data',
        'jquery/ui'
    ],
    function (ko, Element, DeliveryDayUtils, registry, _, $, $tr, moment, tz) {
        "use strict";

        return Element.extend({

            defaults: {
                options: {
                    minDate: 0,
                    maxDate: 365
                },
                defaultValueWasSet: false,
                storeTimeZone: deliveryDateConfig.timezone,
                imports: {
                    dayLimits: 'change_delivery_date_customer_form.change_delivery_date_customer_form_data_source:data.day_limits'
                },
                exports: {
                    activeTimeLimits: 'change_delivery_date_customer_form.change_delivery_date_customer_form_data_source:data.active_time_limits'
                },
                autocomplete: 'off'
            },

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                var self = this;

                this._super();

                this.options.beforeShowDay = function (date) {
                    var diff = DeliveryDayUtils.getDayIndexFromToday(date, self.outputDateFormat),
                        dayLimits = self.dayLimits();

                    if (_.isUndefined(dayLimits) || _.isUndefined(dayLimits[diff])) {
                        return [false, "", $tr("Unavailable")];
                    } else if (diff === -1) {
                        return [false, "", $tr("Unavailable")];
                    } else if (deliveryDateConfig.display_with_extra_charge) {
                        return [true, "", $tr("Available")];
                    } else if ((dayLimits[diff]['extra_charge'] && _.isEmpty(dayLimits[diff]['time_limits']))
                        || self.allLimitsChargeable(dayLimits[diff]['time_limits'])
                    ) {
                        return [false, "", $tr("Unavailable")];
                    } else {
                        return [true, "", $tr("Available")];
                    }
                };

                return this;
            },

            /**
             * Check is all time limits are chargeable (has extra charge)
             *
             * @param limits
             * @returns {boolean}
             */
            allLimitsChargeable: function allLimitsChargeable(limits) {
                if (!limits || limits.length < 1) {
                    return false;
                }

                var BreakException = {};
                try {
                    limits.forEach(function (element) {
                        if (!element.extra_charge) {
                            throw BreakException;
                        }
                    });
                } catch (e) {
                    if (e !== BreakException) {
                        throw e;
                    }

                    return false;
                }

                return true;
            },

            initObservable: function () {
                this._super();
                this.observe('dateFormat activeMethodData defaultValueWasSet dayLimits selectedDay placeholder ' +
                    'activeTimeLimits autocomplete');
                this.placeholder(deliveryDateConfig.day.placeholder);

                return this;
            },

            /**
             * Prepares and sets date/time value that will be displayed
             * in the input field.
             *
             * @param {String} value
             */
            onValueChange: function (value) {
                this._super(value);
                if (value) {
                    var diff = DeliveryDayUtils.getDayIndexFromToday(value, this.outputDateFormat),
                        dayLimits = this.dayLimits(),
                        dayLimit = dayLimits ? dayLimits[diff] : [],
                        timeLimits = dayLimit ? dayLimit['time_limits'] : [];

                    this.activeTimeLimits(timeLimits);

                    this.validateInput(value);
                }
            },

            /**
             * Validate selected delivery date
             *
             * @param date
             */
            validateInput: function validateInput(date) {
                var self = this,
                    index = DeliveryDayUtils.getDayIndexFromToday(date, this.outputDateFormat),
                    selectedDay = self.dayLimits()[index] || null,
                    dateUnavailableError = $tr('Selected date is not available');
                if (!selectedDay && this.value() != '') {
                    this.value('');
                    $('#delivery_date').val($tr('Please, select a desired delivery date from the list of available'));
                    setTimeout(function () {
                        self.error(dateUnavailableError);
                    }, 10);
                }
            },

            /**
             * Select day
             *
             * @param day
             * @param value
             * @param Event
             * @returns {boolean}
             */
            selectDay: function (day, value, Event) {
                var date = DeliveryDayUtils.createDateObjectFromDayIndexFromToday(
                    day,
                    this.pickerDateTimeFormat
                );

                if (typeof date !== 'undefined' && date !== '') {
                    this.selectedDay(day);
                    this.value(date);
                    if (date !== '' && date !== null) {
                        $('#delivery_date').val(date);
                        this.validateInput(date);
                    }
                }

                return true;
            },

            /**
             * Set default value if needed
             */
            initDefaultValue: function () {
                if (!this.defaultValueWasSet()) {
                    this.setDefaultValue();
                }
            },

            /**
             * Change value to default one
             */
            setDefaultValue: function () {
                var dayLimits = this.dayLimits() ? this.dayLimits() : [],
                    index = Object.keys(dayLimits)[0];

                if (typeof index !== 'undefined') {
                    this.selectDay(index);
                    this.defaultValueWasSet(true);
                }
            },

            /**
             * Returns date value as a date string (not like an day-index from today)
             *
             * @returns {number}
             */
            getRealDateValue: function () {
                return this.value();
            }
        });
    }
);
