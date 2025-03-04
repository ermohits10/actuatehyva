define(
    [
        'ko',
        'Magento_Ui/js/form/element/select',
        'uiRegistry',
        'moment'
    ],
    function (ko, Element, registry, moment) {
        "use strict";

        return Element.extend({

            defaults: {
                imports: {
                    activeTimeLimits: 'change_delivery_date_customer_form.change_delivery_date_customer_form_data_source:data.active_time_limits'
                }
            },

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                this._super();

                registry.set('deliveryTimeSelect', this);

                return this;
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe('visible value activeTimeLimits timeLabelFormat');
                this.setInitialValues();
                this.initSubscribers();

                return this;
            },

            setInitialValues: function () {
                // Hide by default
                this.visible(false);

                // Set time format
                if (deliveryDateConfig && deliveryDateConfig.time_label_format) {
                    this.timeLabelFormat(deliveryDateConfig.time_label_format);
                } else {
                    this.timeLabelFormat('{{from_time_24}} - {{to_time_24}}');
                }
            },

            selectTime: function (time) {
                this.value(time);
            },

            /**
             * All subscribers used in this class
             */
            initSubscribers: function () {
                // Update options
                this.activeTimeLimits.subscribe(function (value) {
                    var options = [],
                        defaultValue = null;

                    if (value && value.length > 0) {
                        value.forEach(function (element) {
                            if (!deliveryDateConfig.display_with_extra_charge && element.extra_charge) {
                                return;
                            }
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

                    this.setOptions(options);
                    this.value(defaultValue);
                    this.visible(options.length);
                }, this);
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
                    formattedLabel += ' +' + element.extra_charge + '';
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
            }
        });
    }
);
