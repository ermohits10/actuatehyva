define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function (Button, registry) {
    'use strict';

    return Button.extend({
        defaults: {
            label: ''
        }
    });
});
