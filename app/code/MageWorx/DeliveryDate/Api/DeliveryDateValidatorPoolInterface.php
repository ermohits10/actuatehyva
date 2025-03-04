<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

use Magento\Framework\Exception\ValidatorException;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;

interface DeliveryDateValidatorPoolInterface
{
    /**
     * Validate selected delivery date through all registered validators
     *
     * @param DeliveryDateDataInterface $deliveryDateData
     * @param DeliveryOptionInterface $deliveryOption
     * @return void
     * @throws ValidatorException
     */
    public function validateAll(
        DeliveryDateDataInterface $deliveryDateData,
        DeliveryOptionInterface   $deliveryOption
    ): void;

    /**
     * Add new validator to pool
     *
     * @param DeliveryDateValidatorInterface $validator
     * @return DeliveryDateValidatorPoolInterface
     */
    public function add(DeliveryDateValidatorInterface $validator): DeliveryDateValidatorPoolInterface;

    /**
     * Get all registered validators
     *
     * @return DeliveryDateValidatorInterface[]
     */
    public function get(): array;
}
