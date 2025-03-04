<?php
namespace WeltPixel\GA4\Model\Api;

/**
 * Class \WeltPixel\GA4\Model\Api\ConversionTracking
 */
class ConversionTracking extends \WeltPixel\GA4\Model\Api
{

    /**
     * Variable names
     */
    const VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE = 'WP - Conversion Value';
    const VARIABLE_CONVERSION_TRACKING_ORDER_ID = 'WP - Order ID';
    const VARIABLE_CONVERSION_TRACKING_CUSTOMER_EMAIL = 'WP - GA4 - Customer - Email';
    const VARIABLE_CONVERSION_TRACKING_CUSTOMER_PHONE = 'WP - GA4 - Customer - Phone';
    const VARIABLE_CONVERSION_TRACKING_CUSTOMER_USER_PROVIDED_DATA = 'WP - GA4 - User Provided Data';

    /**
     * Trigger names
     */
    const TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE = 'WP - Magento Checkout Success Page';

    /**
     * Tag names
     */
    const TAG_CONVERSION_TRACKING_ADWORDS_CONVERSION_TRACKING = 'WP - AdWords Conversion Tracking';

    /**
     * Field names used in sending data to dataLayer
     */
    const FIELD_CONVERSION_TRACKING_CONVERSION_VALUE = 'wp_conversion_value';
    const FIELD_CONVERSION_TRACKING_ORDER_ID = 'wp_order_id';
    const FIELD_CONVERSION_TRACKING_CUSTOMER_EMAIL = 'customerEmail';
    const FIELD_CONVERSION_TRACKING_CUSTOMER_PHONE = 'customerPhone';
    const FIELD_CONVERSION_TRACKING_NEW_CUSTOMER = 'new_customer';
    const FIELD_CONVERSION_TRACKING_CUSTOMER_LIFETIME_VALUE = 'customer_lifetime_value';

    /**
     * Return list of variables for conversion tracking
     * @return array
     */
    private function _getConversionVariables()
    {
        $variables = [
            self::VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE => [
                'name' => self::VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE,
                'type' => self::TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => self::FIELD_CONVERSION_TRACKING_CONVERSION_VALUE
                    ]
                ]
            ],
            self::VARIABLE_CONVERSION_TRACKING_ORDER_ID => [
                'name' => self::VARIABLE_CONVERSION_TRACKING_ORDER_ID,
                'type' => self::TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => self::FIELD_CONVERSION_TRACKING_ORDER_ID
                    ]
                ]
            ]
        ];

        return $variables;
    }

    /**
     * Return list of variables for enhanced conversion tracking
     * @return array
     */
    private function _getEnhancedConversionVariables()
    {
        $variables = [
            self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_EMAIL => [
                'name' => self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_EMAIL,
                'type' => self::TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => self::FIELD_CONVERSION_TRACKING_CUSTOMER_EMAIL
                    ]
                ]
            ],
            self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_PHONE => [
                'name' => self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_PHONE,
                'type' => self::TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => self::FIELD_CONVERSION_TRACKING_CUSTOMER_PHONE
                    ]
                ]
            ],
            self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_USER_PROVIDED_DATA => [
                'name' => self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_USER_PROVIDED_DATA,
                'type' => self::TYPE_VARIABLE_AWEC,
                'parameter' => [
                    [
                        'type' => 'template',
                        'key' => 'mode',
                        'value' => "MANUAL"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'phone_number',
                        'value' => "{{" . self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_PHONE . "}}"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'email',
                        'value' => "{{" . self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_EMAIL . "}}"
                    ]
                ]
            ]
        ];

        return $variables;
    }

    /**
     * Return list of triggers for conversion tracking
     * @return array
     */
    private function _getConversionTriggers()
    {
        $triggers = [
            self::TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE => [
                'name' => self::TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE,
                'type' => self::TYPE_TRIGGER_PAGEVIEW,
                'filter' => [
                    [
                        'type' => 'contains',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{Page URL}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => '/checkout/onepage/success'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $triggers;
    }

    /**
     * Return a list of tags for conversion tracking
     * @param array $triggers
     * @param array $params
     * @return array
     */
    private function _getConversionTags($triggers, $params)
    {
        $adwordsConversionTrackingTagParameters = [
            [
                'type' => 'boolean',
                'key' => 'enableConversionLinker',
                'value' => "true"
            ],
            [
                'type' => 'template',
                'key' => 'conversionValue',
                'value' => '{{' . self::VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE . '}}'
            ],
            [
                'type' => 'template',
                'key' => 'orderId',
                'value' => '{{' . self::VARIABLE_CONVERSION_TRACKING_ORDER_ID . '}}'
            ],
            [
                'type' => 'template',
                'key' => 'conversionId',
                'value' => $params['conversion_id']
            ],
            [
                'type' => 'template',
                'key' => 'currencyCode',
                'value' => $params['conversion_currency_code']
            ],
            [
                'type' => 'template',
                'key' => 'conversionLabel',
                'value' => $params['conversion_label']
            ],
            [
                'type' => 'template',
                'key' => 'conversionCookiePrefix',
                'value' => '_gcl'
            ]
        ];

        if ($params['enable_enhanced_conversion']) {
            array_push($adwordsConversionTrackingTagParameters,
                [
                    'type' => 'boolean',
                    'key' => 'enableEnhancedConversion',
                    'value' => 'true'
                ],
                [
                    'type' => 'boolean',
                    'key' => 'enableProductReporting',
                    'value' => 'false'
                ],
                [
                    'type' => 'template',
                    'key' => 'cssProvidedEnhancedConversionValue',
                    'value' => '{{' . self::VARIABLE_CONVERSION_TRACKING_CUSTOMER_USER_PROVIDED_DATA . '}}'
                ],
                [
                    'type' => 'boolean',
                    'key' => 'enableShippingData',
                    'value' => 'false'
                ],
                [
                    'type' => 'boolean',
                    'key' => 'enableNewCustomerReporting',
                    'value' => 'false'
                ]);
        }

        $tags = [
            self::TAG_CONVERSION_TRACKING_ADWORDS_CONVERSION_TRACKING => [
                'name' => self::TAG_CONVERSION_TRACKING_ADWORDS_CONVERSION_TRACKING,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE]
                ],
                'type' => self::TYPE_TAG_AWCT,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => $adwordsConversionTrackingTagParameters
            ]
        ];

        return $tags;
    }

    /**
     * @return array
     */
    public function getConversionVariablesList()
    {
        return $this->_getConversionVariables();
    }


    /**
     * @return array
     */
    public function getEnhancedConversionVariablesList()
    {
        return $this->_getEnhancedConversionVariables();
    }

    /**
     * @return array
     */
    public function getConversionTriggersList()
    {
        return $this->_getConversionTriggers();
    }

    /**
     * @param array $triggers
     * @param array $params
     * @return array
     */
    public function getConversionTagsList($triggers, $params)
    {
        return $this->_getConversionTags($triggers, $params);
    }
}
