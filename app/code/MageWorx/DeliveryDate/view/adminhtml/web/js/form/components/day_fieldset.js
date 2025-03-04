/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/components/fieldset',
    'underscore',
    'ko',
    'uiRegistry'
], function (AbstractField, _, ko, registry) {
    'use strict';

    return AbstractField.extend({

        initObservable: function () {
            this._super();

            return this;
        }

    });
});
