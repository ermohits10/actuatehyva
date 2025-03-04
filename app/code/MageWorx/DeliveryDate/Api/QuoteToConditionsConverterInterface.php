<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Convert quote object (and additional data fields) to the delivery option conditions object.
 * Map specified in the di.xml and must look like:
 * [
 *     "field_name" => [
 *          "getter" => "getFieldName", // from quote
 *          "setter" => "setFieldName" // to conditions object
 *      ],
 *      "another_field_name" => [
 *          "getter" => "getUsingCustomMethodFromQuote", // from quote
 *          "setter" => "setUsingCustomMethodToConditions" // to conditions object
 *      ]
 * ]
 */
interface QuoteToConditionsConverterInterface
{
    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param array|null $additionalData
     * @return DeliveryOptionConditionsInterface
     * @throws LocalizedException
     */
    public function convert(
        \Magento\Quote\Api\Data\CartInterface $quote,
        ?array                                $additionalData = []
    ): DeliveryOptionConditionsInterface;

    /**
     * @return array
     */
    public function getMap(): array;

    /**
     * Convert snake case property name to getter method
     *
     * @param string $field
     * @return string
     */
    public function convertPropertyToGetter(string $field): string;
}
