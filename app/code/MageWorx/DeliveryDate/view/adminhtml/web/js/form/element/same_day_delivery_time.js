/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'jquery/ui',
    'customTimePicker'
], function (AbstractField, $) {
    'use strict';

    return AbstractField.extend({

        defaults: {
            elementTmpl: 'MageWorx_DeliveryDate/form/element/time',
            shiftedValue: '',
            options: {
                timeFormat: 'HH:mm',
                interval: 60,
                dynamic: false,
                dropdown: false,
                scrollbar: false,
                zindex: 9001
            }
        },

        initTimepicker: function () {
            var $element = $('#'+this.uid),
                self = this,
                config = this.options;

            config.change = function(time) {
                var element = $(this),
                    timepicker = element.customTimePicker();
                self.value(timepicker.format(time));
            };

            $element.customTimePicker(config);
        }
    });
});
