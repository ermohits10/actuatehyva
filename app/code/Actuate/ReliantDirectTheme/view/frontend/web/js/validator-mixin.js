define([
    'jquery',
    'jquery/validate'
], function ($) {
    "use strict";

    return function (validator) {
        validator.addRule('validate-zip-uk', function (v, e) {
            return $.mage.isEmptyNoTrim(v) || /^[A-Z]{1,2}[0-9RCHNQ][0-9A-Z]?\s[0-9][ABD-HJLNP-UW-Z]{2}$|^[A-Z]{2}-?[0-9]{4}$/.test(v);
        }, $.mage.__("Please enter a valid zip code (Ex: ST3 5PQ, AB54 3XN, M1 3BR)."));

        return validator;
    };
});