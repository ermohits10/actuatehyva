"use strict";
define(
    [
        'ko',
        'Magento_Ui/js/form/element/select',
        'MageWorx_DeliveryDate/js/model/delivery-date-utils',
        'MageWorx_DeliveryDate/js/model/delivery-data-storage',
        'uiRegistry',
        'underscore',
        'jquery',
        'mage/translate',
        'moment',
        'moment-timezone-with-data',
        'jquery/ui'
    ],
    function (
        ko,
        Element,
        deliveryDateUtils,
        deliveryDateDataStorage,
        registry,
        _,
        $,
        $t,
        moment,
        tz
    ) {
        "use strict";

        return Element.extend({
            defaults: {
                template: 'ui/form/field',
                elementTmpl: 'MageWorx_DeliveryDate/delivery_date/element/date/dropdown',
                autocomplete: 'off',
                additionalChargeMessage: '',
                valueObject: {},
                extraCharge: '',
                caption: $t('Select Delivery Date'),
                captionValue: '',
                options: [],
                imports: {
                    dates: '${ $.deliveryOptionsStorage }:options',
                    isVisible: "${ $.deliveryOptionsStorage }:optionsLength"
                },
                exports: {
                    extraCharge: '${ $.provider }:extraCharge'
                }
            },

            observableProperties: [
                'isVisible',
                'dates',
                'autocomplete',
                'additionalChargeMessage',
                'valueObject',
                'extraCharge',
                'caption',
                'uid'
            ],

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                this._super()
                    .initSubscribers()
                    .initPreRenderFunctions();

                return this;
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe(this.observableProperties);

                return this;
            },

            initSubscribers: function () {
                /**
                 * Update values when dates array has been changed
                 */
                this.dates.subscribe(function (dates) {
                    // Create & Set options
                    this.parseOptionsFromDatesArray(dates);

                    // Set default value
                    if (!this.value()) {
                        this.initDefaultValue();
                    }

                    if (!this.isDateAvailable(this.value())) {
                        // Reset value in case it is not in a list of available dates
                        this.reset();
                    } else {
                        // Trigger value update in case if value has not been changed. Need for time limits update.
                        this.value.notifySubscribers(this.value());
                    }
                }.bind(this));

                this.value.subscribe(function (value) {
                    // Check empty value
                    if (value === undefined || value === '') {
                        this.valueObject({});
                    } else {
                        let selectedDayIndex = deliveryDateUtils.getDayIndexFromToday(value),
                            selectedDay = {};

                        if (selectedDayIndex !== -1) {
                            selectedDay = this.getDayConfigByIndex(selectedDayIndex);
                        }

                        // Updating values for time component
                        this.valueObject(selectedDay);

                        // Saving current date in temp. storage
                        deliveryDateDataStorage.setSelectedDay(value);
                    }
                }.bind(this));

                if (mwDeliveryDateConfig['reload_shipping_rates']) {
                    this.valueObject.subscribe(function (dateConfig) {
                        this.extraCharge(dateConfig.extra_charge ?? '');
                    }.bind(this));
                }

                return this;
            },

            parseOptionsFromDatesArray: function (dates) {
                let values = [];

                // Set caption if not required
                this.caption(!mwDeliveryDateConfig.required);
                if (!this.captionValue) {
                    this.captionValue = '';
                }

                values.push({
                    'label': $t('Please, select delivery date.'),
                    'value': ''
                });

                // Prepare options from dates array
                for (let key in dates) {
                    if (dates.hasOwnProperty(key)) {
                        values.push({
                            'label': dates[key].date_formatted,
                            'value': dates[key].date
                        });
                    }
                }

                // Set options to dropdown
                this.setOptions(values);
            },

            /**
             * Functions which must be defined before the component has been rendered
             */
            initPreRenderFunctions: function () {
                if (mwDeliveryDateConfig.day_input_label) {
                    this.label = mwDeliveryDateConfig.day_input_label;
                }

                return this;
            },

            initDefaultValue: function () {
                let origValue = this.value(),
                    storageValue = deliveryDateDataStorage.getSelectedDay();

                if (_.isEmpty(origValue)) {
                    // No date selected
                    if (!_.isEmpty(storageValue) && this.isDateAvailable(storageValue)) {
                        // Date exists in session (local) storage: select date from storage
                        this.value(storageValue);
                    } else if (this.isPreselectedDateEnabled()) {
                        let firstAvailableValue = this.getFirstAvailableValue();
                        if (firstAvailableValue) {
                            // Select first available date from set
                            this.value(firstAvailableValue);
                        }
                    }
                }

                return this;
            },

            /**
             *
             * @returns {boolean}
             */
            isPreselectedDateEnabled: function () {
                return Boolean(mwDeliveryDateConfig.preselected);
            },

            /**
             * Get first available date from list of currently active dates
             *
             * @returns {*}
             */
            getFirstAvailableValue: function () {
                let dates = this.dates(),
                    firstDate;

                if (!_.isEmpty(dates) && !_.isEmpty(dates[0]) && !_.isEmpty(dates[0]['date'])) {
                    firstDate = dates[0]['date'];
                }

                return firstDate;
            },

            /**
             * @returns {*|jQuery|HTMLElement}
             */
            getElementByUid: function () {
                return $("#" + this.uid());
            },

            /**
             * Is specified date available in the list of provided dates (from API)
             *
             * @param date
             * @returns {boolean}
             */
            isDateAvailable: function (date) {
                let diff = deliveryDateUtils.getDayIndexFromToday(date),
                    res,
                    result = false;

                if (diff === -1) {
                    return result;
                }

                res = this.getDayConfigByIndex(diff);
                if (!_.isEmpty(res)) {
                    result = true;
                }

                return result;
            },

            /**
             * Get configuration of day by day index (from today)
             *
             * @param dayIndex
             * @returns {{}|*}
             */
            getDayConfigByIndex: function (dayIndex) {
                let dates = this.dates();
                if (!dates || _.isEmpty(dates)) {
                    return {};
                }

                let res = _.findWhere(dates, {day_index: dayIndex});
                if (res === undefined) {
                    return {};
                }

                return res;
            },

            /**
             * Validates itself by it's validation rules using validator object.
             * If validation of a rule did not pass, writes it's message to
             * 'error' observable property.
             *
             * @returns {Object} Validate information.
             */
            validate: function () {
                // Skip validation of hidden or disabled component
                if (!this.isVisible() || this.disabled()) {
                    return {
                        valid: true,
                        target: this
                    };
                }

                let resultOrig = this._super();
                if (!resultOrig.valid) {
                    return resultOrig;
                }

                // If original validation has been passed perform additional validation
                let value = this.value(),
                    result = this.performAdditionalValidation(value),
                    message = !this.disabled() && this.visible() ? result.message : '',
                    isValid = this.disabled() || !this.visible() || result.passed;

                this.error(message);
                this.error.valueHasMutated();
                this.bubble('error', message);

                if (this.source && !isValid) {
                    this.source.set('params.invalid', true);
                }

                return {
                    valid: isValid,
                    target: this
                };
            },

            /**
             * Additional validation for date input
             *
             * @param value
             * @returns {{passed: boolean, message: string}|{passed: boolean, message}}
             */
            performAdditionalValidation: function (value) {
                if (value) {
                    let isValid = this.isDateAvailable(value);

                    if (!isValid) {
                        return {
                            passed: false,
                            message: $t('Selected date is not available. Please, select another date.')
                        }
                    }
                }

                return {
                    passed: true,
                    message: ''
                }
            }
        });
    }
);
