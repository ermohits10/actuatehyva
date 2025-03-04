/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/provider',
    'underscore',
    'mage/translate'
], function (AbstractProvider, _, $t) {
    'use strict';

    return AbstractProvider.extend({
        defaults: {
            storageConfig: {
                component: 'MageWorx_DeliveryDate/js/grid/queue_orders_data_storage',
                provider: '${ $.storageConfig.name }',
                name: '${ $.name }_storage',
                updateUrl: '${ $.update_url }'
            }
        }
    });
});