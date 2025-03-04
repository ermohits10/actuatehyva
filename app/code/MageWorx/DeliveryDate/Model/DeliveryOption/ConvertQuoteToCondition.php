<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\DeliveryOption;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use MageWorx\DeliveryDate\Api\Data\DeliveryOptionConditionsDataInterfaceFactory as ConditionsFactory;
use MageWorx\DeliveryDate\Api\DeliveryOptionConditionsInterface;

/**
 * Convert customer quote to delivery option condition object
 */
class ConvertQuoteToCondition implements \MageWorx\DeliveryDate\Api\QuoteToConditionsConverterInterface
{
    /**
     * @var ConditionsFactory
     */
    protected $conditionsFactory;

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var array|string[]
     */
    protected $allowedDataTypes = ["bool", "boolean", "int", "integer", "float", "double", "string", "null"];

    /**
     * @param ConditionsFactory $conditionsFactory
     */
    public function __construct(
        ConditionsFactory $conditionsFactory,
        array             $map = [],
        array             $allowedDataTypes = []
    ) {
        $this->conditionsFactory = $conditionsFactory;
        $this->map               = $map;
        $this->allowedDataTypes  = array_merge($this->allowedDataTypes, $allowedDataTypes);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|Quote $quote
     * @param array|null $additionalData
     * @return DeliveryOptionConditionsInterface
     * @throws LocalizedException
     */
    public function convert(\Magento\Quote\Api\Data\CartInterface $quote, ?array $additionalData = []): DeliveryOptionConditionsInterface
    {
        /** @var DeliveryOptionConditionsInterface $conditions */
        $conditions = $this->conditionsFactory->create();
        $map        = $this->getMap();

        foreach ($map as $field => $methods) {
            if (!isset($methods['getter']) || !isset($methods['setter'])) {
                throw new LocalizedException(
                    __('Delivery Date Exception: get and set methods must be provided for the field %1', $field)
                );
            }

            $getterProvided = $methods['getter'];
            $setterProvided = $methods['setter'];

            // Get data from source object
            $getterName = $this->convertPropertyToGetter($field);
            $extensionAttributes = $quote->getExtensionAttributes();
            if (isset($additionalData[$field])) {
                // Try to get data from additional data array because it has maximum priority
                $data = $additionalData[$field];
            } elseif (method_exists($quote, $getterProvided)) {
                // Directly call method if exists
                $data = $quote->{$getterProvided}();
            } elseif ($quote->getData($field)) {
                // Call magic getter method if real method does not exist
                $data = $quote->getData($field);
            } elseif ($extensionAttributes && method_exists($extensionAttributes, $getterName)) {
                // Trying to locate attribute by getter in the extension attributes object
                $extensionAttributes->{$getterName}();
            } else {
                // no data provided, continue
                continue;
            }

            // Force convert type if required
            if (isset($methods['forced_simple_type'])) {
                $allowedTypes = $this->getAllowedDataType();

                $type = (string)$methods['forced_simple_type'];
                if (in_array($type, $allowedTypes)) {
                    settype($data, $type);
                }
            }

            if (!method_exists($conditions, $setterProvided)) {
                throw new LocalizedException(
                    __(
                        'Delivery Date Exception: setter method "%1" does not exist in the conditions model.',
                        $setterProvided
                    )
                );
            }

            // Set value to the conditions object
            $conditions->{$setterProvided}($data);
        }

        return $conditions;
    }

    /**
     * @return array
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * Convert snake case property name to getter method
     *
     * @param string $field
     * @return string
     */
    public function convertPropertyToGetter(string $field): string
    {
        $parts   = explode('_', $field);
        $ucParts = array_map('ucfirst', $parts);
        $method  = 'get' . implode('', $ucParts);

        return $method;
    }

    /**
     * @return array|string[]
     */
    public function getAllowedDataType(): array
    {
        return $this->allowedDataTypes;
    }
}
