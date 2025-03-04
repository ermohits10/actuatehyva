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
                visible: false,
                defaultValue: null,
                template: 'ui/form/field',
                autocomplete: false,
                additionalChargeMessage: '',
                dayConfig: {},
                extraCharge: '',
                imports: {
                    dayConfig: '${ $.parentName }.delivery_day:valueObject'
                },
                exports: {
                    extraCharge: '${ $.provider }:extraCharge'
                }
            },

            observableProperties: [
                'isVisible',
                'visible',
                'defaultValue',
                'timeLabelFormat',
                'autocomplete',
                'additionalChargeMessage',
                'dayConfig',
                'extraCharge',
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
                this.dayConfig.subscribe(function (dayConfig) {
                    if (_.isEmpty(dayConfig)) {
                        this.reset();
                        this.visible(false);
                    } else {
                        let options = this.getTimeLimitOptions(dayConfig);

                        // Setting default value before setting options, because it trigger of set default value
                        this.initDefaultValue();
                        this.options(options);
                        this.value(this.defaultValue());
                        this.visible(options.length);
                    }
                }.bind(this));

                this.value.subscribe(function (value) {
                    if (value !== undefined && value !== '') {
                        // Save selected time to storage
                        deliveryDateDataStorage.setSelectedTime(value);

                        // Save extra charge of selected time slot to trigger update of shipping rates
                        // (change shipping rate price)
                        if (mwDeliveryDateConfig['reload_shipping_rates']) {
                            let selectedOption = this.getOptionByValue(value);
                            if (selectedOption !== null) {
                                this.extraCharge(selectedOption.extra_charge ?? '');
                            } else {
                                this.extraCharge('');
                            }
                        }
                    }
                }.bind(this));

                return this;
            },

            /**
             * Functions which must be defined before the component has been rendered
             */
            initPreRenderFunctions: function () {
                if (_.isUndefined(mwDeliveryDateConfig.time_label_format)) {
                    this.timeLabelFormat('{{from_time_24}} - {{to_time_24}}');
                } else {
                    this.timeLabelFormat(mwDeliveryDateConfig.time_label_format);
                }

                if (mwDeliveryDateConfig.time_input_label) {
                    this.label = mwDeliveryDateConfig.time_input_label;
                }

                return this;
            },

            initDefaultValue: function () {
                let origValue = this.value(),
                    storageValue = deliveryDateDataStorage.getSelectedTime();

                if (_.isEmpty(origValue) && !_.isEmpty(storageValue)) {
                    this.defaultValue(storageValue);
                    this.default = storageValue;
                }

                return this;
            },

            getTimeLimitOptions: function (dayConfig) {
                let options = [],
                    timeLimits = dayConfig['time_limits'] || [],
                    defaultValue = null,
                    previousValue = this.value();

                timeLimits.forEach(function (element) {
                    let value = element.from + '_' + element.to;
                    options.push({
                        'value': value,
                        'label': this.getTimeLabelFormatted(element),
                        'extra_charge': element.extra_charge
                    });
                    if (defaultValue === null) {
                        defaultValue = value;
                    }
                    if (previousValue === value) {
                        defaultValue = previousValue;
                    }
                }.bind(this));

                this.defaultValue(defaultValue);

                return options;
            },

            /**
             * Format time label using template defined in a store configuration
             *
             * @param element
             * @returns {String}
             */
            getTimeLabelFormatted: function (element) {
                let formattedLabel,
                    format = this.timeLabelFormat(),
                    from12 = this.convert24to12hoursFormat(element.from),
                    to12 = this.convert24to12hoursFormat(element.to);

                formattedLabel = format.replace('{{from_time_24}}', element.from)
                    .replace('{{to_time_24}}', element.to)
                    .replace('{{from_time_12}}', from12)
                    .replace('{{to_time_12}}', to12);

                if (element.extra_charge !== undefined && element.extra_charge !== '') {
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

            getOptionByValue: function (value) {
                let options = this.options();

                for (let key in options) {
                    if (options.hasOwnProperty(key)) {
                        if (options[key].value === value) {
                            return options[key];
                        }
                    }
                }

                return null;
            }
        });
    }
);
