<?php

namespace Actuate\ReliantDirectTheme\Plugin\Block;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Options;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\AttributeMapper;

class LayoutProcessor
{
    /**
     * @var AttributeMetadataDataProvider
     */
    public AttributeMetadataDataProvider $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
public AttributeMapper $attributeMapper;

    /**
     * @var AttributeMerger
     */
public AttributeMerger $merger;

    /**
     * @var CheckoutSession
     */
public CheckoutSession $checkoutSession;

    /**
     * @var null
     */
public ?\Magento\Quote\Model\Quote $quote = null;

    /**
     * @var Options
     */
    public $options;

    /**
     * @var Data
     */
private Data $checkoutDataHelper;

    /**
     * LayoutProcessor constructor.
     *
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     * @param CheckoutSession $checkoutSession
     * @param Data $checkoutDataHelper
     * @param Options|null $options
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        CheckoutSession $checkoutSession,
        Data $checkoutDataHelper,
        Options $options = null
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutDataHelper = $checkoutDataHelper;
        $this->options = $options ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Customer\Model\Options::class);
    }

    /**
     * Get Quote
     *
     * @return \Magento\Quote\Model\Quote|null
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayoutResult
     * @return array
     * @throws LocalizedException
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayoutResult
    ): array {
        if ($this->getQuote()->isVirtual()) {
            return $jsLayoutResult;
        }

        $attributesToConvert = [
            'prefix' => [$this->options, 'getNamePrefixOptions'],
            'suffix' => [$this->options, 'getNameSuffixOptions'],
        ];

        if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset'])) {
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['company']['label'] = __('Company (Optional)');

            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['notice'] = __('We will use this number to send you a 2 hour delivery time slot');

            $elements = $this->getAddressAttributes();
            $elements = $this->convertElementsToSelect($elements, $attributesToConvert);
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['billing-address'] = $this->getCustomBillingAddressComponent($elements);
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['billing-address']['children']['form-fields']['children']['company']['label'] = __('Company (Optional)');
        }

        //Remove billing address from payment step
        if ($this->checkoutDataHelper->isDisplayBillingOnPaymentMethodAvailable()) {
            if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'])) {
                $paymentListChildren = $jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'];

                foreach ($paymentListChildren as $childKey=>$childInfos) {
                    if (!empty($childInfos['component']) && $childInfos['component'] == 'Magento_Checkout/js/view/billing-address') {
                        unset($paymentListChildren[$childKey]);
                    }
                }
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'] = $paymentListChildren;
            }
        } else {
            if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['afterMethods']['children']['billing-address-form'])) {
                unset($jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['afterMethods']['children']['billing-address-form']);
            }
        }

        if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['customer-email']['children']['amazon-pay-button-region'])) {
            $amazonComponent = $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['customer-email']['children']['amazon-pay-button-region'];
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['amazon-pay-button-region'] = $amazonComponent;
            unset($amazonComponent);
        }


        return $jsLayoutResult;
    }

    /**
     * Get all visible address attribute
     *
     * @return array
     * @throws LocalizedException
     */
    private function getAddressAttributes()
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
        }
        return $elements;
    }

    /**
     * Prepare billing address field for shipping step for physical product
     *
     * @param $elements
     * @return array
     */
    public function getCustomBillingAddressComponent($elements)
    {
        return [
            'component' => 'Magento_Checkout/js/view/billing-address',
            'displayArea' => 'billing-address',
            'provider' => 'checkoutProvider',
            'deps' => ['checkoutProvider'],
            'dataScopePrefix' => 'billingAddress',
            'children' => [
                'form-fields' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'additional-fieldsets',
                    'children' => $this->merger->merge(
                        $elements,
                        'checkoutProvider',
                        'billingAddress',
                        [
                            'country_id' => [
                                'sortOrder' => 115,
                            ],
                            'region' => [
                                'visible' => false,
                            ],
                            'region_id' => [
                                'component' => 'Magento_Ui/js/form/element/region',
                                'config' => [
                                    'template' => 'ui/form/field',
                                    'elementTmpl' => 'ui/form/element/select',
                                    'customEntry' => 'billingAddress.region',
                                ],
                                'validation' => [
                                    'required-entry' => true,
                                ],
                                'filterBy' => [
                                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                    'field' => 'country_id',
                                ],
                            ],
                            'postcode' => [
                                'component' => 'Magento_Ui/js/form/element/post-code',
                                'validation' => [
                                    'required-entry' => true,
                                ],
                            ],
                            'company' => [
                                'validation' => [
                                    'min_text_length' => 0,
                                ],
                            ],
                            'fax' => [
                                'validation' => [
                                    'min_text_length' => 0,
                                ],
                            ],
                            'telephone' => [
                                'config' => [
                                    'tooltip' => [
                                        'description' => __('For delivery questions.'),
                                    ],
                                ],
                            ],
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Convert elements(like prefix and suffix) from inputs to selects when necessary
     *
     * @param array $elements address attributes
     * @param array $attributesToConvert fields and their callbacks
     * @return array
     */
    private function convertElementsToSelect($elements, $attributesToConvert): array
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (!in_array($code, $codes)) {
                continue;
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $options = call_user_func($attributesToConvert[$code]);
            if (!is_array($options)) {
                continue;
            }
            $elements[$code]['dataType'] = 'select';
            $elements[$code]['formElement'] = 'select';

            foreach ($options as $key => $value) {
                $elements[$code]['options'][] = [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        }

        return $elements;
    }
}