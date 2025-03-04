<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\Validator;

use Magento\Framework\Exception\ValidatorException;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;
use MageWorx\DeliveryDate\Api\DeliveryDateValidatorInterface;
use MageWorx\DeliveryDate\Api\DeliveryDateValidatorPoolInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;

class DeliveryDateValidatorPool implements DeliveryDateValidatorPoolInterface
{
    /**
     * @var DeliveryDateValidatorInterface[]
     */
    protected $validators = [];

    /**
     * @param DeliveryDateValidatorInterface[] $validators
     */
    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     * @throws ValidatorException
     */
    public function validateAll(
        DeliveryDateDataInterface $deliveryDateData,
        DeliveryOptionInterface $deliveryOption
    ): void {
        foreach ($this->get() as $validator) {
            $validator->validate($deliveryDateData, $deliveryOption);
        }
    }

    /**
     * @inheritDoc
     */
    public function add(DeliveryDateValidatorInterface $validator): DeliveryDateValidatorPoolInterface
    {
        $this->validators[] = $validator;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        return $this->validators;
    }
}
