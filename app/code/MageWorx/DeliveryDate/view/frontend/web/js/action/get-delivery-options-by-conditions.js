define(
    [
        'jquery',
        'MageWorx_DeliveryDate/js/model/url-builder',
        'mage/storage'
    ],
    function (
        $,
        urlBuilder,
        storage
    ) {
        "use strict";

        return {
            getDeliveryOptions: function (conditions) {
                return storage.post(
                    urlBuilder.createUrl('/delivery-date-limits-by-conditions', {}),
                    JSON.stringify({"deliveryOptionConditions": conditions})
                ).always(
                    function () {
                        // Make inputs available in case when spinner disables it and didn't turn it back on
                        $('[name="delivery_day"]').prop('disabled', false);
                        $('[name="delivery_time"]').prop('disabled', false);
                    }
                );
            }
        }
    }
)
