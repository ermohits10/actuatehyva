/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Ui/js/model/messageList',
    'jquery'
], function (
    registry,
    quote,
    urlBuilder,
    storage,
    errorProcessor,
    customer,
    globalMessageContainer,
    $
) {
    'use strict';

    var timerId;

    /**
     * Get shipping method from the quote in format carrierCode_methodCode
     *
     * @returns {string}
     */
    function getShippingMethodFromQuote() {
        let shippingMethod = quote.shippingMethod();
        if (!shippingMethod) {
            return ''; // no shipping method yet
        }

        return shippingMethod.carrier_code + '_' + shippingMethod.method_code;
    }

    /**
     * Prepare payload request according API
     * @see \MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface
     *
     * @param deliveryDateData
     * @returns {{}}
     */
    function prepareData(deliveryDateData) {
        let data = {};

        data['delivery_option'] = deliveryDateData['delivery_option_id'];
        data['delivery_day'] = deliveryDateData['delivery_day'];
        data['delivery_comment'] = deliveryDateData['delivery_comment'];
        data['delivery_time'] = deliveryDateData['delivery_time'];
        data['shipping_method'] = deliveryDateData['shipping_method'] ?? getShippingMethodFromQuote();

        return data;
    }

    /**
     * Perform saving process
     *
     * @param deliveryDate
     */
    function saveDeliveryDate(deliveryDate) {
        let serviceUrl,
            payload,
            headers = {},
            data;

        data = prepareData(deliveryDate);

        payload = {
            cartId: quote.getQuoteId(),
            deliveryDateData: data,
            address: getAddress()
        };

        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/delivery-date', {
                cartId: quote.getQuoteId()
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/delivery-date', {});
        }

        let messageContainer = registry.get('checkout.errors')
            ? registry.get('checkout.errors').messageContainer
            : globalMessageContainer;

        storage.post(
            serviceUrl, JSON.stringify(payload), true, 'application/json', headers
        ).fail(
            function (response) {
                errorProcessor.process(response, messageContainer);
            }
        ).always(
            function () {
                // Make inputs available in case when spinner disables it and didn't turn it back on
                $('[name="delivery_day"]').prop('disabled', false);
                $('[name="delivery_time"]').prop('disabled', false);
            }
        );
    }

    /**
     * Get actual shipping address from data provider
     *
     * @returns {{}}
     */
    function getAddress() {
        let provider = registry.get('deliveryDateProvider'),
            address = {};

        if (provider && provider.conditions && provider.conditions.address) {
            address = provider.conditions.address;
        }

        return address;
    }

    return function () {
        // Prevent many saves on each change
        clearTimeout(timerId);
        timerId = setTimeout(function () {
            let deliveryDateProvider = registry.get('deliveryDateProvider');
            if (deliveryDateProvider) {
                let deliveryDate = deliveryDateProvider.get('delivery_date');

                saveDeliveryDate(deliveryDate);
            } else {
                console.log('ERROR [Delivery Date]: unable to locate delivery date provider');
            }
        }, 500);
    };
});
