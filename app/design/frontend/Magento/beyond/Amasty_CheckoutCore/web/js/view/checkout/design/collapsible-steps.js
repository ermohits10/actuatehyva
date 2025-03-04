define([
    'jquery',
    'mage/collapsible',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    $.widget('amasty.checkoutCollapsibleSteps', {

        saveAndContinueSelector: '.action-save-continue',
        stepContainerSelector: '.amcheckout-step-container',
        stepTitleSelector: '.amcheckout-title',

        _create: function () {
            var self = this;
            this.createCollapsible();

            $(this.saveAndContinueSelector).on('click', function() {
                let stepTitle = $(this).closest(self.stepContainerSelector);
                if (!stepTitle.find('.checkout-billing-address').find('fieldset.fieldset').is(":hidden")) {
                    stepTitle.find('.checkout-billing-address').find('.action-update').trigger('click');
                    if (stepTitle.find('.checkout-billing-address').find('form').find('.field._error').length > 0) {
                        return false;
                    }
                }
                if (stepTitle.find(self.stepTitleSelector).parent().hasClass('-opened')) {
                    stepTitle.find(self.stepTitleSelector).trigger('click');
                    if (!stepTitle.next().find(self.stepTitleSelector).parent().hasClass('-opened')) {
                        stepTitle.next().find(self.stepTitleSelector).trigger('click');
                    }
                }
            });
        },

        initCollapsible: function () {
            var self = this;

            $(this.element).parent().collapsible({
                header: '[data-amcheckout-js="step-title"]',
                content: '[data-amcheckout-js="step-content"]',
                active: $(this.element).parent().hasClass('checkout-shipping-address'),
                openedState: '-collapsible -opened',
                closedState: '-collapsible -closed',
                icons: {
                    header: 'amcheckout-icon -plus',
                    activeHeader: 'amcheckout-icon -minus'
                },
                animate: 300
            });
            $(this.element).parent().on("dimensionsChanged", function (event, data) {
                var opened = data.opened;

                if (opened) {
                    $(self.element).parent().find('.tab-details').hide();
                } else {
                    $(self.element).parent().find('.tab-details').show();
                }
            });
        },

        createCollapsible: function () {
            var checkoutWindow = window;

            if (!$(this.element).parent().data('mageCollapsible')
                && checkoutWindow.checkoutDesign == 'modern'
                && (checkoutWindow.checkoutLayout == '1column' || checkoutWindow.checkoutLayout == '2columns')) {
                this.initCollapsible();
            }
        },

        destroyCollapsible: function () {
            var collapsibleElement = $(this.element).parent();

            if (collapsibleElement.data('mageCollapsible')) {
                collapsibleElement.collapsible("forceActivate").collapsible("destroy");
            }
        }
    });

    return $.amasty.checkoutCollapsibleSteps;
});
