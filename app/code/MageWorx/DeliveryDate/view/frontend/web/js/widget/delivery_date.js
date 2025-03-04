/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/translate',
    'jquery/ui',
    'mage/validation'
], function ($, _, $tr) {
    'use strict';

    $.widget('mage.deliveryDateWidget', {
        options: {
            deliveryDaySelector: '#delivery-day-input',
            deliveryTimeSelector: '#delivery-time-input',
            deliveryOptionIdSelector: '#delivery-option-id-input',
            deliveryCommentSelector: '#delivery-comment-input',
            deliveryTimeContainerSelector: '#delivery-time-input-container',
            containerSelector: '#block-delivery',
            requiredValue: false,
            preselectedValue: false,
            validDeliveryDate: false,
            updateDeliveryDate: true
        },

        _init: function () {
            var self = this;
            this._super();
            if (!_.isUndefined(this.options.config.mageworx.delivery_date)) {
                this.options.requiredValue = this.options.config.mageworx.delivery_date.required;
                this.options.preselectedValue = this.options.config.mageworx.delivery_date.preselected;
                this.options.optionsByMethod = this.options.config.mageworx.delivery_date;
                var $shippingMethodsSelect = $('#shipping-method');
                self.selectedMethod = $shippingMethodsSelect.val();

                // Update when shipping method was changed
                $shippingMethodsSelect.on('change', function () {
                    self.selectedMethod = $(this).val();
                    self.updateDayValues(self.selectedMethod);
                });

                // Update time limits when day changed
                var $ddSelect = $(self.options.deliveryDaySelector);
                $ddSelect.on('change', function () {
                    self.updateTimeSelectorValues($(this).val());
                });

                // Run Initial update
                self.updateDayValues(self.selectedMethod);
                self.updateTimeSelectorValues($ddSelect.val());
            } else {
                // Hide whole container when there was no delivery options available
                $(self.options.containerSelector).hide();
            }
        },

        /**
         * Update time selector
         *
         * @param index
         * @returns {*|jQuery|HTMLElement}
         */
        updateTimeSelectorValues: function (index) {
            var self = this,
                $dtSelect = $(self.options.deliveryTimeSelector),
                $dtContainer = $(self.options.deliveryTimeContainerSelector),
                methodCode = self.selectedMethod,
                ddConfig = self.options.config.mageworx.delivery_date;

            $dtSelect.empty().val('');

            if (!methodCode) {
                $dtContainer.hide();

                return $dtSelect;
            }

            var data = _.isUndefined(ddConfig[methodCode]) ||
            _.isUndefined(ddConfig[methodCode]['day_limits'][index]) ||
            _.isUndefined(ddConfig[methodCode]['day_limits'][index]['time_limits']) ||
            ddConfig[methodCode]['day_limits'][index]['time_limits'].length < 1 ?
                {} :
                ddConfig[methodCode]['day_limits'][index]['time_limits'];

            var firstTime = true;

            // TODO: Changes
            if (!this.options.preselectedValue) {
                var initialValue = '',
                    optionElementData = {
                        value: '',
                        class: 'dt-option'
                    };

                optionElementData.selected = 'selected';
                firstTime = false;

                $option = $('<option/>', optionElementData);
                $option.html($tr('Please select a value...'));
                $dtSelect.append($option);
            }
            // End

            for (var key in data) {
                var value = data[key],
                    from = value.from ? value.from : 0,
                    to = value.to ? value.to : 24,
                    composedValue = from + '_' + to,
                    optionElementData = {
                        value: composedValue,
                        class: 'dt-option'
                    };

                if (firstTime) {
                    optionElementData.selected = 'selected';
                    firstTime = false;
                    var initialValue = composedValue;
                }

                var $option = $('<option/>', optionElementData),
                    extraCharge = value.extra_charge ? '<span class="data-item__price">+' + value.extra_charge + '</span>' : '';
                $option.html('from ' + from + ' to ' + to + extraCharge);
                $dtSelect.append($option);
            }

            if (data.length > 0) {
                $dtContainer.show();
                if (initialValue) {
                    $dtSelect.val(initialValue);
                }
            } else {
                $dtSelect.val('');
                $dtContainer.hide();
            }

            return $dtSelect;
        },

        /**
         * Update options of the day selector
         *
         * @param method
         * @returns {*|mage.deliveryDateWidget}
         */
        updateDayValues: function (method) {
            if (!this.options.updateDeliveryDate) {
                return;
            }

            var self = this,
                $ddSelect = $(self.options.deliveryDaySelector),
                $dtSelect = $(self.options.deliveryTimeSelector),
                $doIdInput = $(self.options.deliveryOptionIdSelector),
                $container = $(self.options.containerSelector);

            self.options.validDeliveryDate = false;

            if (!method) {
                $container.hide();

                return self.clearValues();
            }

            var options = self.options.optionsByMethod;
            if (!options || _.isUndefined(options[method]) || options[method].length < 1) {
                $container.hide();

                return self.clearValues();
            }

            var optionsByMethod = options[method];

            $ddSelect.empty();
            $dtSelect.empty();

            var dayLimits = !_.isUndefined(optionsByMethod.day_limits) ? optionsByMethod.day_limits : {};
            if (dayLimits.length < 1) {
                $container.hide();
            }

            var firstTime = true;

            // TODO: Changes
            if (!this.options.preselectedValue) {
                var initialValue = '',
                    optionElementData = {
                        value: '',
                        class: 'dd-option'
                    };

                optionElementData.selected = 'selected';
                firstTime = false;

                $option = $('<option/>', optionElementData);
                $option.data('time_limits', JSON.stringify({}));
                $option.html($tr('Please select a value...'));
                $ddSelect.append($option);
            }
            // End

            for (var index in dayLimits) {
                var value = dayLimits[index],
                    optionElementData = {
                        value: index,
                        class: 'dd-option'
                    };

                if (firstTime) {
                    optionElementData.selected = 'selected';
                    firstTime = false;
                    var initialValue = index;
                }

                var $option = $('<option/>', optionElementData);

                $option.data('time_limits', JSON.stringify(value));
                $option.html(value.date_formatted);
                $ddSelect.append($option);
            }

            if (initialValue) {
                $ddSelect.val(initialValue);
                self.updateTimeSelectorValues(initialValue);
            }

            if (!_.isUndefined(optionsByMethod['entity_id'])) {
                $doIdInput.val(optionsByMethod['entity_id']);
            }

            if (this.options.preselectedValue) {
                this.submitDeliveryInfo();
            }

            $container.show();
        },

        /**
         * Get formatted day from today
         *
         * @param days
         * @returns {string}
         */
        dayFromToday: function (days) {
            var date = new Date(),
                intDays = parseInt(days),
                dateDays = date.getDate(),
                dateDaysPlusDays = dateDays + intDays,
                options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };

            date.setDate(dateDaysPlusDays);

            return date.toLocaleDateString("en-US", options);
        },

        /**
         * Clear values of the select
         *
         * @returns {mage.deliveryDateWidget}
         */
        clearValues: function () {
            $(this.options.deliveryDaySelector).val('')
                .empty();
            $(this.options.deliveryTimeSelector).val('')
                .empty();

            return this;
        },

        /**
         * Hide or show place order button
         *
         * @param disable
         * @returns {*}
         */
        toggleCheckoutButton: function (disable) {
            var $buttonsContainer = $('#review-buttons-container'),
                $button = $('#review-button'),
                message = $tr('Please, select delivery date'),
                messageElementId = 'dd-notification',
                $messageElement = $('#' + messageElementId);

            if (disable) {
                $button.hide();
                if ($messageElement.length === 0) {
                    $buttonsContainer.append('<div class="primary" id="' + messageElementId + '">' + message + '</div>');
                }
            } else {
                $messageElement.remove();
                $button.show();
            }

            return this;
        },

        /**
         * @private
         */
        _create: function () {
            var self = this;

            setInterval(function () {
                if (self.options.requiredValue) {
                    self.toggleCheckoutButton(!self.options.validDeliveryDate);
                }
            }, 300);

            $(this.options.deliveryDateSubmitButton).on('click', $.proxy(function () {
                if (this.element.validation().valid()) {
                    self.submitDeliveryInfo();
                }
            }, this));
        },

        setValidDeliveryDate: function setValidDeliveryDate (value) {
            this.options.validDeliveryDate = Boolean(value);
        },

        submitDeliveryInfo: function () {
            var deliveryDateSpinnerId, messages, self = this;

            deliveryDateSpinnerId = $(this.options.deliveryDateSpinnerId);
            messages = this.options.messages;
            var dayFormatted = self.dayFromToday($(this.options.deliveryDaySelector).val());

            $.ajax({
                url: this.options.submitUrl,
                type: 'post',
                cache: false,
                data: {
                    'delivery_day': dayFormatted,
                    'delivery_time': $(this.options.deliveryTimeSelector).val(),
                    'delivery_comment': $(this.options.deliveryCommentSelector).val(),
                    'delivery_option_id': $(this.options.deliveryOptionIdSelector).val()
                },

                /**
                 * Before send.
                 */
                beforeSend: function () {
                    deliveryDateSpinnerId.show();
                },

                /**
                 * @param {*} response
                 */
                success: function (response) {
                    $(messages).hide();
                    if (response.success && self.options.updateDeliveryDate) {
                        self.setValidDeliveryDate(true);
                        self.options.updateDeliveryDate = false;
                        updateTotals();
                        self.options.updateDeliveryDate = true;
                    }
                    self.toggleCheckoutButton(!self.options.validDeliveryDate);
                },

                error: function (err) {
                    self.options.validDeliveryDate = false;
                },

                /**
                 * Complete.
                 */
                complete: function () {
                    deliveryDateSpinnerId.hide();
                }
            });
        }
    });

    function updateTotals() {
        try {
            var widget = $("#order-review-form").data("mageOrderReview"),
                defaultShippingMethod = 'flatrate_flatrate';

            if ($('#shipping-method').length > 0) {
                widget._updateOrderHandler();
            } else {
                $('#shipping-method-form').prepend(
                    $('<select name="shipping_method" id="shipping-method"><option value="' + defaultShippingMethod + '" selected="selected"></option></select>')
                );

                var isShippingSubmitFormOriginalValue = widget.isShippingSubmitForm;
                widget.isShippingSubmitForm = true;
                widget._submitUpdateOrder(
                    $(widget.options.shippingSubmitFormSelector).prop('action'),
                    widget.options.updateContainerSelector
                );
                widget.isShippingSubmitForm = isShippingSubmitFormOriginalValue;
                $('#shipping-method').remove();
            }
        } catch (e) {
            console.log(e);
        }
    }

    return $.mage.deliveryDateWidget;
});
