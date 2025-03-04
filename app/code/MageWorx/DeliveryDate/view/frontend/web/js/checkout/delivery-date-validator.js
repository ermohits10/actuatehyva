/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/translate',
    'mage/validation'
], function ($, _, registry, $t) {
    'use strict';

    return {
        validate: function () {
            var isValid = true,
                deliveryDateContainerPath = '#delivery-datetime-container:visible',
                deliveryDayInput = registry.get('index = delivery_day'),
                value = null;

            if (_.isUndefined(mwDeliveryDateConfig.required)
                || $(deliveryDateContainerPath).length === 0
                || !deliveryDayInput
            ) {
                return true;
            }

            value = deliveryDayInput.value();

            if (value === '' || value === -1 || value === null) {
                isValid = false;
                deliveryDayInput.error($t('This is a required field.'));
                $(deliveryDateContainerPath).get(0).scrollIntoView();
            }

            if (isValid) {
                deliveryDayInput.error(null);
            }

            return isValid;
        }
    };
});
