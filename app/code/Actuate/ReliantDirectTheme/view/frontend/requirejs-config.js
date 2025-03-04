let config = {
    "config": {
        "mixins": {
            'Magento_Ui/js/lib/validation/validator': {
                'Actuate_ReliantDirectTheme/js/validator-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Actuate_ReliantDirectTheme/js/model/checkout-data-resolver-mixin': false
            },
            'Magento_Checkout/js/view/shipping': {
                'Actuate_ReliantDirectTheme/js/mixin/shipping-mixin': false
            },
            'Amazon_Payment/js/view/shipping': {
                'Actuate_ReliantDirectTheme/js/mixin/shipping-amazon-mixin': false
            }
        }
    }
};
