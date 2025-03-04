/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/components/fieldset',
    'underscore',
    'ko'
], function (AbstractField, _, ko) {
    'use strict';

    return AbstractField.extend({

        initObservable: function () {
            this._super();
            this.observe('invisible limitedQuotes');

            this.invisible.subscribe(function (value) {
                this.visible(!value);
            }, this);

            return this;
        }

    });
});
