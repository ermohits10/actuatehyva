define([
    'jquery',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'underscore',
    'mage/translate',
    'moment',
    'moment-timezone-with-data',
    'MageWorx_DeliveryDate/js/checkout/element/date/deliveryDayUtils',
    'jsDate'
], function (
    $,
    ko,
    Component,
    registry,
    quote,
    _,
    $t,
    moment,
    tz,
    deliveryDateUtils
) {
    'use strict';

    return Component.extend({

        defaults: {
            exports: {
                disableDateSelection: '${ $.parentName }.datetime_container.delivery_day:disabled',
                disableTimeSelection: '${ $.parentName }.datetime_container.delivery_time:disabled'
            },
            activeMethodData: [],
            dayLimits: [],
            previousShippingMethod: null,
            debug: false,
            disableDateSelection: false,
            disableTimeSelection: false,
            isError: false,
            errorMessage: $t('Delivery Date is required')
        },

        showErrorFlag: mwDeliveryDateConfig.required,
        preselectFirstAvailableDate: mwDeliveryDateConfig.preselected ?? false,

        observableProperties: [
            'activeMethodData',
            'dayLimits',
            'timeLabelFormat',
            'previousShippingMethod',
            'disableDateSelection',
            'disableTimeSelection',
            'isError',
            'errorMessage',
            'selectedDay',
            'dayComponent'
        ],

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            registry.set('deliveryDateManager', this);

            return this;
        },

        initObservable: function () {
            var self = this;

            this._super();
            this.observe(this.observableProperties);

            if (_.isUndefined(mwDeliveryDateConfig.time_label_format)) {
                this.timeLabelFormat('{{from_time_24}} - {{to_time_24}}');
            } else {
                this.timeLabelFormat(mwDeliveryDateConfig.time_label_format);
            }

            quote.shippingMethod.subscribe(function (method) {
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null,
                    activeMethodData = [],
                    ddContainer = this.getDeliveryDateContainer();

                if (this.name.indexOf('store-pickup') > -1 && selectedMethod !== 'instore_pickup') {
                    return selectedMethod; // skip processing for pickup tab DD clone on other tab
                }

                if (typeof ddContainer !== 'undefined') {
                    if (!_.isEmpty(mwDeliveryDateConfig[selectedMethod])) {
                        activeMethodData = mwDeliveryDateConfig[selectedMethod];
                    }

                    if (selectedMethod && !_.isEmpty(activeMethodData)) {
                        if (selectedMethod !== this.previousShippingMethod()) {
                            this.previousShippingMethod(selectedMethod);
                            this.activeMethodData(activeMethodData);

                            if (_.size(this.dayLimits()) > 0) {
                                this.showDeliveryDate();
                            } else {
                                this.hideDeliveryDate();
                            }
                            this.updateLimits(selectedMethod, mwDeliveryDateConfig[selectedMethod]);
                            if (this.preselectFirstAvailableDate) {
                                this.setDeliveryDayDefaultValue();
                            }
                        }
                        if (!_.isUndefined(mwDeliveryDateConfig[selectedMethod] &&
                            mwDeliveryDateConfig[selectedMethod]['entity_id'])
                        ) {
                            this.saveDeliveryOptionIdInAddress(mwDeliveryDateConfig[selectedMethod]['entity_id']);
                        }
                    } else {
                        this.hideDeliveryDate();
                    }
                }

                return selectedMethod;
            }, this);

            this.selectedDay.subscribe(function (date) {
                var dayIndex = null;

                if (date === "0" || date === 0 || String(date).length <= 3) {
                    dayIndex = parseInt(date);
                } else if (date) {
                    dayIndex = self.getDayIndexFromToday(date);
                }

                self.calculateTimeLimitOptions(date, dayIndex);
                self.detectExtraChargeForDay(dayIndex);
            });

            this.activeMethodData.subscribe(function (value) {
                if (!value || value.length < 1) {
                    self.dayLimits([]);
                    return;
                }

                self.dayLimits(value.day_limits);

                // enable or disable date and time selection components based on delivery option settings
                var disableSelectionByCustomer = Boolean(Number(value['disable_selection']));
                if (mwDeliveryDateConfig.preselected) {
                    self.disableDateSelection(disableSelectionByCustomer);
                    self.disableTimeSelection(disableSelectionByCustomer);
                } else {
                    if (disableSelectionByCustomer) {
                        console.log('Delivery Date pre selection is disabled or ' +
                            'date cannot be automatically selected.');
                    }
                    self.disableDateSelection(false);
                    self.disableTimeSelection(false);
                }
            }, this);

            return this;
        },

        updateLimits: function updateLimits(selectedMethod, data) {
            if (_.isUndefined(data)) {
                return;
            }

            if (!_.isUndefined(data['entity_id'])) {
                this.saveDeliveryOptionIdInAddress(data['entity_id']);
            }

            this.updateLimitsInCalendar(data);
        },

        /**
         * Add extra charge message for each day if there is no time limits
         *
         * @param dayIndex
         */
        detectExtraChargeForDay: function (dayIndex) {
            var deliveryDayComponent = this.getDeliveryDayComponent(),
                dayLimits = this.dayLimits() || [],
                dayData = dayLimits[dayIndex] || [],
                timeLimits = dayData.time_limits || [],
                extraChargeForDay = 0,
                extraChargeMessageForDay = '';

            if (_.isEmpty(timeLimits)) {
                extraChargeForDay = dayData['extra_charge'];
                extraChargeMessageForDay = dayData['extra_charge_message'];
            } else {
                extraChargeForDay = 0;
                extraChargeMessageForDay = '';
            }

            if (deliveryDayComponent) {
                deliveryDayComponent.additionalCharge(extraChargeForDay);
                deliveryDayComponent.additionalChargeMessage(extraChargeMessageForDay);
            }
        },

        /**
         * Update time limit options if exists
         *
         * @param date
         * @param {Number} dayIndex
         * @returns {Array}
         */
        calculateTimeLimitOptions: function (date, dayIndex) {
            var timeRangeSelect = this.getDeliveryTimeComponent(),
                options = [];

            var dayLimits = this.dayLimits() || [],
                dayData = dayLimits[dayIndex] || [],
                timeLimits = dayData.time_limits || [],
                defaultValue = null;

            if (timeRangeSelect) {
                if (date) {
                    timeLimits.forEach(function (element) {
                        var value = element.from + '_' + element.to;
                        options.push({
                            'value': value,
                            'label': this.getTimeLabelFormatted(element)
                        });
                        if (defaultValue === null) {
                            defaultValue = value;
                        }
                    }.bind(this));
                }

                timeRangeSelect.options(options);
                if (mwDeliveryDateConfig.preselected) {
                    timeRangeSelect.value(defaultValue);
                }
                timeRangeSelect.visible(options.length);
            }

            return timeLimits;
        },

        /**
         * Format time label using template defined in a store configuration
         *
         * @param element
         * @returns {String}
         */
        getTimeLabelFormatted: function (element) {
            var formattedLabel,
                format = this.timeLabelFormat(),
                from12 = this.convert24to12hoursFormat(element.from),
                to12 = this.convert24to12hoursFormat(element.to);

            formattedLabel = format.replace('{{from_time_24}}', element.from)
                .replace('{{to_time_24}}', element.to)
                .replace('{{from_time_12}}', from12)
                .replace('{{to_time_12}}', to12);

            if (element.extra_charge) {
                if (!_.isUndefined(mwDeliveryDateConfig.time.input_type) && (
                    mwDeliveryDateConfig.time.input_type === 'dropdown' ||
                    mwDeliveryDateConfig.time.input_type === 'default'
                )) {
                    formattedLabel += ' +' + element.extra_charge + '';
                } else {
                    formattedLabel += '<span class="data-item__price"> +' + element.extra_charge + '</span>';
                }
            }

            return formattedLabel;
        },

        /**
         *
         * @param time24
         * @returns {string|*}
         */
        convert24to12hoursFormat: function (time24) {
            var hours24 = moment(time24, "H:m"),
                hours12 = hours24.format("h:mm a");

            hours12 = hours12.replace('am', 'a.m.');
            hours12 = hours12.replace('pm', 'p.m.');

            return hours12;
        },

        showDeliveryDate: function showDeliveryDate() {
            this.getDeliveryDateContainer().isVisible(true);
            this.initCommentField();
            this.hideErrorContainer();
        },

        hideDeliveryDate: function hideDeliveryDate() {
            this.getDeliveryDateContainer().isVisible(false);
            this.showErrorContainer();
        },

        showErrorContainer: function () {
            var errorMessage;

            if (this.showErrorFlag &&
                this.activeMethodData() &&
                this.activeMethodData()['entity_id']
            ) {
                this.isError(true);
                errorMessage = this.activeMethodData()['error_message'];
                if (errorMessage) {
                    this.errorMessage(errorMessage);
                }
            } else {
                this.hideErrorContainer();
            }
        },

        hideErrorContainer: function () {
            this.isError(false);
        },

        /**
         * Get delivery date element and set default value to it  (forced)
         */
        setDeliveryDayDefaultValue: function setDeliveryDayDefaultValue() {
            var deliveryDayInput = this.getDeliveryDayComponent(),
                self = this;
            if (deliveryDayInput) {
                if (typeof deliveryDayInput.initDefaultValue == 'function') {
                    deliveryDayInput.initDefaultValue();
                    if (!deliveryDayInput.value()) {
                        this.disableDateSelection(false);
                        this.disableTimeSelection(false);
                    } else {
                        var date = deliveryDayInput.value(),
                            dayIndex;
                        if (date === "0" || date === 0 || String(date).length <= 3) {
                            dayIndex = parseInt(date);
                        } else if (date) {
                            dayIndex = self.getDayIndexFromToday(date);
                        }
                        this.calculateTimeLimitOptions(date, dayIndex);
                    }
                } else {
                    deliveryDayInput.clear();
                    this.disableDateSelection(false);
                    this.disableTimeSelection(false);
                }
            }
        },

        initCommentField: function () {
            this.updateCommentFieldVisibility();
            this.updateCommentFieldLabel();
        },

        updateCommentFieldVisibility: function () {
            if (!mwDeliveryDateConfig.comment_field_visible) {
                var deliveryComment = this.getCommentComponent();//registry.get('index = delivery_comment');
                if (deliveryComment) {
                    deliveryComment.visible(false);
                }
            }
        },

        updateCommentFieldLabel: function () {
            var deliveryComment = this.getCommentComponent(),//registry.get('index = delivery_comment'),
                label = _.isUndefined(mwDeliveryDateConfig.comment_field_label) ?
                    null :
                    mwDeliveryDateConfig.comment_field_label;
            if (deliveryComment && deliveryComment.visible() && label) {
                $('#' + deliveryComment.uid).closest('div.field')
                    .find('label.label span:first')
                    .html(label);
            }
        },

        getDeliveryTimeComponent: function getDeliveryTimeComponent() {
            try {
                return this.getDeliveryDateContainer()
                    .getChild('datetime_container')
                    .getChild('delivery_time');
            } catch (e) {
                return null;
            }
        },

        getDeliveryDayComponent: function getDeliveryDayComponent() {
            try {
                return this.getDeliveryDateContainer()
                    .getChild('datetime_container')
                    .getChild('delivery_day');
            } catch (e) {
                return null;
            }
        },

        getDeliveryOptionIdComponent: function getDeliveryOptionIdComponent() {
            try {
                return this.getDeliveryDateContainer()
                    .getChild('delivery_option_id');
            } catch (e) {
                return null;
            }
        },

        getCommentComponent: function getCommentComponent() {
            return this.getDeliveryDateContainer() &&
                this.getDeliveryDateContainer().getChild('comment_container') &&
                this.getDeliveryDateContainer().getChild('comment_container').getChild('delivery_comment');
        },

        getDeliveryDateContainer: function getDeliveryDateContainer() {
            if (window.isMageWorxCheckout) {
                if (quote.shippingMethod() && quote.shippingMethod().method_code === 'mageworxpickup') {
                    return registry.get('checkout.steps.billing-step.pickup_container.mageworxpickup.shipping_method_additional_data.delivery_date');
                } else {
                    return registry.get('checkout.steps.shipping-step.shippingMethods.shipping_method_additional_data.delivery_date');
                }
            } else if (quote.shippingMethod() && (quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code']) === 'instore_pickup') {
                return registry.get('checkout.steps.store-pickup.store-selector.delivery_date');
            } else {
                return registry.get('checkout.steps.shipping-step.shippingAddress.shippingAdditional.delivery_date');
            }
        },

        getDayIndexFromToday: function getDayIndexFromToday(date) {
            var today, diff, dateMoment,
                now = mwDeliveryDateConfig.now;

            today = new Date(now);

            dateMoment = new Date(moment(date).toDate());
            dateMoment.setHours(today.getHours());
            dateMoment.setMinutes(today.getMinutes() + 1);

            diff = deliveryDateUtils.getDiffInDays(dateMoment, today);

            if (diff < 0) {
                return -1;
            } else if (diff === 0) {
                var result = dateMoment.getFullYear() === today.getFullYear() &&
                    dateMoment.getMonth() === today.getMonth() &&
                    dateMoment.getDay() === today.getDay();

                if (result) {
                    return 0;
                } else {
                    return -1;
                }
            }

            return diff;
        },

        /**
         * Save delivery option id in the custom attributes of the shipping address
         *
         * @param id
         */
        saveDeliveryOptionIdInAddress: function (id) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress) {
                var doIdInput = this.getDeliveryOptionIdComponent();
                if (doIdInput) {
                    doIdInput.value(id);
                }
            }
        },

        /**
         * If calendar exists - updates its settings according to the config
         *
         * @param data
         */
        updateLimitsInCalendar: function (data) {
            var $calendar = $('#' + this.getCalendarId()),
                dayLimits;

            if ($calendar.length > 0 && typeof $calendar.datepicker != 'undefined') {
                dayLimits = data.day_limits;

                if (typeof dayLimits !== 'undefined' && _.size(dayLimits) > 0) {
                    this.showDeliveryDate();
                } else {
                    this.hideDeliveryDate();
                }

                $calendar.datepicker("option", "minDate", data.start_days_limit - 1);
                $calendar.datepicker("option", "maxDate", data.future_days_limit + 1);
                $calendar.datepicker("refresh");

                if (!this.preselectFirstAvailableDate) {
                    this.clearValues();
                }
            }
        },

        /**
         * Clear input values
         */
        clearValues: function () {
            var $calendar = $('#' + this.getCalendarId()),
                deliveryDayInput = this.getDeliveryDayComponent(),
                deliveryTimeInput = this.getDeliveryTimeComponent();

            if ($calendar.length > 0) {
                $calendar.val('');
            }

            if (deliveryDayInput) {
                deliveryDayInput.reset();
                this.selectedDay('');
            }

            if (deliveryTimeInput) {
                deliveryTimeInput.hide();
                deliveryTimeInput.reset();
            }
        },

        getCalendarId: function () {
            return this.getDeliveryDayComponent() ? this.getDeliveryDayComponent().uid() : 'delivery-date';
        }
    });
});
