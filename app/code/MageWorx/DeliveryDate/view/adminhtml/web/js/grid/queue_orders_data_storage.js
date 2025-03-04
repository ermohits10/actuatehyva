/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/data-storage',
    'jquery',
    'underscore',
    'mageUtils',
    'mage/translate'
], function (AbstractDataStorage, $, _, utils, $t) {
    'use strict';

    return AbstractDataStorage.extend({

        /**
         * Sends request to the server with provided parameters.
         *
         * @param {Object} params - Request parameters.
         * @returns {jQueryPromise}
         */
        requestData: function (params) {
            if (!_.isEmpty(params) && _.isEmpty(params.filters.delivery_option_id)) {
                params.delivery_option_id = window.mageworx.delivery_date.config.list.delivery_option.id;
            }

            return this._super(params);
        }
    });
});