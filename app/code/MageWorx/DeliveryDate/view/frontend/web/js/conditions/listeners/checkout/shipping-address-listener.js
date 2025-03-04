define(
    [
        'jquery',
        'uiComponent',
        'uiRegistry',
        'Magento_Customer/js/model/customer'
    ],
    function (
        $,
        Component,
        registry,
        customer
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    value: null,
                    code: 'address',
                    mainListener: null,
                    normalizedAddressValue: {},
                    trackedFields: [
                        'postcode',
                        'country_id'
                    ],
                    copyFields: [
                        'city',
                        'company',
                        'countryId',
                        'customerId',
                        'firstname',
                        'lastname',
                        'middlename',
                        'postcode',
                        'region',
                        'regionId',
                        'street',
                        'telephone',
                        'vatId'
                    ],
                    exports: {
                        normalizedAddressValue: "${ $.provider }:conditions.${ $.code }"
                    }
                },

                observableProperties: [
                    'value',
                    'trackedFields',
                    'copyFields',
                    'normalizedAddressValue'
                ],

                initialize: function () {
                    this._super()
                        .initEventObservers();

                    registry.async('index = ddConditionsListener')(function (listener) {
                        listener.addListener(this);
                        if (!customer.isLoggedIn()) {
                            this.normalizedAddressValue.notifySubscribers(this.normalizedAddressValue());
                        }
                    }.bind(this));

                    return this;
                },

                /** @inheritdoc */
                initObservable: function () {
                    this._super()
                        .observe(this.observableProperties);

                    return this;
                },

                initEventObservers: function () {
                    let firstLoad = true;

                    registry.async('checkoutProvider')(function (checkoutProvider) {
                        checkoutProvider.on('shippingAddress', function (shippingAddress) {
                            let needToUpdate = false;

                            this.trackedFields().forEach(function (field) {
                                let newValue,
                                    oldValue,
                                    changed = false;

                                if (shippingAddress && shippingAddress[field]) {
                                    newValue = shippingAddress[field];
                                }

                                if (this.value() && this.value()[field]) {
                                    oldValue = this.value()[field];
                                }

                                changed = newValue !== oldValue;
                                needToUpdate = changed || needToUpdate;
                            }.bind(this));

                            // Update address anyway, do not use reference because we must check changes next time
                            this.value({...shippingAddress});

                            // Update conditions in case something important has been changed
                            if (needToUpdate) {
                                this.updateGlobalConditions();
                            }
                        }.bind(this));

                        // Set address condition on first load
                        if (firstLoad) {
                            // Reset flag
                            firstLoad = false;

                            // Unpack shipping address
                            let shippingAddress = checkoutProvider.shippingAddress;
                            this.value({...shippingAddress});
                            this.updateGlobalConditions();
                        }
                    }.bind(this));
                },

                /**
                 * Update conditions in global object
                 */
                updateGlobalConditions: function () {
                    this.normalizedAddressValue(this.getNormalizedShippingAddressFromValue());
                },

                /**
                 * Copy only selected fields from quote address to customer address data object
                 *
                 * @returns {{}}
                 */
                getNormalizedShippingAddressFromValue: function () {
                    let addressDataObject = {},
                        address = this.value() ?? {};

                    this.copyFields().forEach(function (field) {
                        addressDataObject[field] = address[field] ?? null;
                    });

                    return addressDataObject;
                }
            }
        );
    }
);
