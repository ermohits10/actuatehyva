/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'underscore',
    'mage/translate'
], function (AbstractField, registry, _, $t) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            tracks: {
                sunday: true,
                monday: true,
                tuesday: true,
                wednesday: true,
                thursday: true,
                friday: true,
                saturday: true,
                general: true,
                general_quote_limit: true,
                general_time_limits_quote: true
            },

            exports: {
                sunday: 'delivery_option_form.delivery_option_form.limits.sunday:visible',
                monday: 'delivery_option_form.delivery_option_form.limits.monday:visible',
                tuesday: 'delivery_option_form.delivery_option_form.limits.tuesday:visible',
                wednesday: 'delivery_option_form.delivery_option_form.limits.wednesday:visible',
                thursday: 'delivery_option_form.delivery_option_form.limits.thursday:visible',
                friday: 'delivery_option_form.delivery_option_form.limits.friday:visible',
                saturday: 'delivery_option_form.delivery_option_form.limits.saturday:visible',
                general: 'delivery_option_form.delivery_option_form.limits.default:visible',
                general_quote_limit: 'delivery_option_form.delivery_option_form.limits.default.daily_quotes:visible',
                limitedQuotes: 'delivery_option_form.delivery_option_form.limits.default:limitedQuotes'
            }
        },

        initObservable: function () {
            this._super();
            this.observe('sunday monday tuesday wednesday thursday friday saturday general general_quote_limit limitedQuotes');

            this.value.subscribe(function (value) {
                if (value == 0) {
                    this.sunday(false);
                    this.monday(false);
                    this.tuesday(false);
                    this.wednesday(false);
                    this.thursday(false);
                    this.friday(false);
                    this.saturday(false);
                    this.general(true);
                    this.general_quote_limit(false);
                    this.limitedQuotes(false);
                } else if (value == 1) {
                    this.sunday(false);
                    this.monday(false);
                    this.tuesday(false);
                    this.wednesday(false);
                    this.thursday(false);
                    this.friday(false);
                    this.saturday(false);
                    this.general(true);
                    this.general_quote_limit(true);
                    this.limitedQuotes(true);
                } else {
                    this.general(false);

                    var workingDaysMultiselect = registry.get('index = working_days'),
                        values = [],
                        allValues = false;

                    if (workingDaysMultiselect) {
                        values = workingDaysMultiselect.value();
                    }

                    if (values.length === 0) {
                        allValues = true;
                    }

                    this.sunday(values.indexOf('sunday') !== -1 || allValues);
                    this.monday(values.indexOf('monday') !== -1 || allValues);
                    this.tuesday(values.indexOf('tuesday') !== -1 || allValues);
                    this.wednesday(values.indexOf('wednesday') !== -1 || allValues);
                    this.thursday(values.indexOf('thursday') !== -1 || allValues);
                    this.friday(values.indexOf('friday') !== -1 || allValues);
                    this.saturday(values.indexOf('saturday') !== -1 || allValues);
                }
            }, this);

            return this;
        }
    });
});
