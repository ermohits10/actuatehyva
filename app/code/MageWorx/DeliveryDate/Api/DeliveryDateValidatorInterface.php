<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

use Magento\Framework\Exception\ValidatorException;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;

/**
 * One validator for the delivery date selected by customer.
 */
interface DeliveryDateValidatorInterface
{
    /**
     * @param DeliveryDateDataInterface $deliveryDateData
     * @param DeliveryOptionInterface $deliveryOption
     * @return void
     * @throws ValidatorException
     */
    public function validate(
        DeliveryDateDataInterface $deliveryDateData,
        DeliveryOptionInterface   $deliveryOption
    ): void;
}
