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

            if (config['day']) {
                if (config['day']['component']) {
                    templateData.component = config['day']['component'];
                }

                if (config['day']['config']) {
                    templateData.config = _.extend(templateData.config, config['day']['config']);
                }
            }

            templateData.caption = $t('Select Delivery Date');
            templateData.optionsCaption = $t('Select Delivery Date');
            templateData.placeholder = $t('Select Delivery Date');
            templateData.autocomplete = 'off';

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
