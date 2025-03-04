/**
 * Checkout adapter for customer data storage
 *
 * @api
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mageUtils',
    'jquery/jquery-storageapi'
], function ($, storage, utils) {
    'use strict';

    var cacheKey = 'mw-delivery-data',

        /**
         * @param {Object} data
         */
        saveData = function (data) {
            storage.set(cacheKey, data);
        },

        /**
         * @return {*}
         */
        initData = function () {
            return {
                'selectedDay': null,
                'selectedTime': null,
                'comment': null,
                'surcharge': null,
            };
        },

        /**
         * @return {*}
         */
        getData = function () {
            var data = storage.get(cacheKey)();

            if ($.isEmptyObject(data)) {
                data = $.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey);

                if ($.isEmptyObject(data)) {
                    data = initData();
                    saveData(data);
                }
            }

            return data;
        };

    return {
        /**
         * @param {Object} data
         */
        setSelectedDay: function (data) {
            var obj = getData();

            obj.selectedDay = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getSelectedDay: function () {
            return getData().selectedDay;
        },

        /**
         * @param {Object} data
         */
        setSelectedTime: function (data) {
            var obj = getData();

            obj.selectedTime = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getSelectedTime: function () {
            return getData().selectedTime;
        },

        /**
         * @param {Object} data
         */
        setComment: function (data) {
            var obj = getData();

            obj.comment = data;
            saveData(obj);
        },

        /**
         * @returns {*}
         */
        getComment: function () {
            return getData().comment;
        }
    }
});
