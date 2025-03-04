/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/multiselect',
    'uiRegistry',
    'underscore',
    'ko'
], function (AbstractField, registry, _, ko) {
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
                saturday: true
            },

            exports: {
                sunday: 'delivery_option_form.delivery_option_form.limits.sunday:visible',
                monday: 'delivery_option_form.delivery_option_form.limits.monday:visible',
                tuesday: 'delivery_option_form.delivery_option_form.limits.tuesday:visible',
                wednesday: 'delivery_option_form.delivery_option_form.limits.wednesday:visible',
                thursday: 'delivery_option_form.delivery_option_form.limits.thursday:visible',
                friday: 'delivery_option_form.delivery_option_form.limits.friday:visible',
                saturday: 'delivery_option_form.delivery_option_form.limits.saturday:visible'
            }
        },

        initObservable: function () {
            this._super();
            this.observe('sunday monday tuesday wednesday thursday friday saturday');

            this.value.subscribe(function (values) {
                var dataSource = registry.get('index = delivery_option_form_data_source'),
                    quoteScopeValue = dataSource && typeof dataSource.get('data.general.quotes_scope') !== 'undefined' ?
                        Number.parseInt(dataSource.get('data.general.quotes_scope')) :
                        null;

                if (typeof values === 'string') {
                    values = values.split(',');
                }

                if (values && values.length > 0) {
                    if (quoteScopeValue === 2) {
                        this.sunday(values.indexOf('sunday') !== -1);
                        this.monday(values.indexOf('monday') !== -1);
                        this.tuesday(values.indexOf('tuesday') !== -1);
                        this.wednesday(values.indexOf('wednesday') !== -1);
                        this.thursday(values.indexOf('thursday') !== -1);
                        this.friday(values.indexOf('friday') !== -1);
                        this.saturday(values.indexOf('saturday') !== -1);
                    } else if (quoteScopeValue === 1 || quoteScopeValue === 0) {
                        this.sunday(false);
                        this.monday(false);
                        this.tuesday(false);
                        this.wednesday(false);
                        this.thursday(false);
                        this.friday(false);
                        this.saturday(false);
                    }
                } else {
                    if (quoteScopeValue === 2) {
                        this.sunday(true);
                        this.monday(true);
                        this.tuesday(true);
                        this.wednesday(true);
                        this.thursday(true);
                        this.friday(true);
                        this.saturday(true);
                    }
                }
            }, this);


            return this;
        }
    });
});
