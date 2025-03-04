define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'uiLayout',
        'MageWorx_DeliveryDate/js/model/template_factory/date-template',
        'MageWorx_DeliveryDate/js/model/template_factory/time-template',
        'mageUtils',
        'underscore',
        'jquery/ui'
    ],
    function (ko, $, Component, layout, dateTemplate, timeTemplate, utils, _) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'MageWorx_DeliveryDate/container/datetime',
                isVisible: true,
                hasButtonsClass: false,
                config: {},
                childTemplates: {} // Possible defined in layout xml
            },

            observableProperties: [
                'isVisible',
                'hasButtonsClass',
                'config'
            ],

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                this._super()
                    .initValues()
                    .initChildComponents();

                return this;
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe(this.observableProperties);

                return this;
            },

            initValues: function () {
                let config = mwDeliveryDateConfig || {},
                    initialConfig = this.config() || {},
                    fullConfig = _.extend(initialConfig, config),
                    dateConfig = fullConfig['day']  || {},
                    timeConfig = fullConfig['time'] || {},
                    hasButtons = dateConfig['input_type'] === 'buttons' || timeConfig['input_type'] === 'buttons';

                this.config(fullConfig);
                this.hasButtonsClass(hasButtons);

                return this;
            },

            initChildComponents: function () {
                let template = utils.copy(this.childTemplates),
                    deliveryDayTemplate = template[this.templateElementNames.deliveryDay],
                    deliveryTimeTemplate = template[this.templateElementNames.deliveryTime];

                // Delivery date component
                deliveryDayTemplate = dateTemplate(deliveryDayTemplate, this.templateElementNames.deliveryDay, this);
                deliveryDayTemplate = this.parseTemplateString(deliveryDayTemplate);

                // Delivery time component
                deliveryTimeTemplate = timeTemplate(deliveryTimeTemplate, this.templateElementNames.deliveryTime, this);
                deliveryTimeTemplate = this.parseTemplateString(deliveryTimeTemplate);

                layout([deliveryTimeTemplate, deliveryDayTemplate]);
            },

            /**
             * Parses string templates.
             * Skip parse if deferredTmpl property set to "true"
             *
             * @param {Object} obj
             *
             * @returns {Object} obj
             */
            parseTemplateString: function (obj) {
                var children;

                if (obj.children) {
                    children = utils.copy(obj.children);
                    delete obj.children;
                }

                obj = utils.template(obj, obj);
                obj.children = children;

                if (children) {
                    _.each(children, function (child, name) {
                        obj.children[name] = child.config.deferredTmpl ? child : this.parseTemplateString(child);
                    }, this);
                }

                obj.name = obj.config.name || obj.name;

                return obj;
            },
        });
    }
);
