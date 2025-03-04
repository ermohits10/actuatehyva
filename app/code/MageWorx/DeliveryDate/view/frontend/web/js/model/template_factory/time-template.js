define(
    [
        'jquery',
        'mage/translate',
        'jquery/ui'
    ], function (
        $,
        $t
    ) {
        "use strict";

        /**
         * @return Obj {*}
         */
        return function (templateData, nameInLayout, parent) {
            let config = mwDeliveryDateConfig || {};

            templateData.name = nameInLayout;
            templateData.parent = parent.name ?? parent.name;
            templateData.parentName = parent.name ?? parent.name;

            if (config['time']) {
                if (config['time']['component']) {
                    templateData.component = config['time']['component'];
                }

                if (config['time']['config']) {
                    templateData.config = _.extend(templateData.config, config['time']['config']);
                }
            }

            templateData.caption = $t('Select Delivery Time');
            templateData.placeholder = $t('Select Delivery Time');

            if (config.required === true) {
                let validation = templateData.validation ?? {};

                validation['required-entry'] = true;
                templateData.caption = null;

                templateData.validation = validation;
            }

            return templateData;
        }
    }
);
