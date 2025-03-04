<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\Validator;

use Magento\Framework\Exception\ValidatorException;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;

class ValidatePassDate implements \MageWorx\DeliveryDate\Api\DeliveryDateValidatorInterface
{
    /**
     * @var ValidatorHelper
     */
    protected $validatorHelper;

    public function __construct(
        \MageWorx\DeliveryDate\Model\Validator\ValidatorHelper $validatorHelper
    ) {
        $this->validatorHelper = $validatorHelper;
    }

    /**
     * @inheritDoc
     */
    public function validate(DeliveryDateDataInterface $deliveryDateData, DeliveryOptionInterface $deliveryOption): void
    {
        $date = $deliveryDateData->getDeliveryDay();
        if ($date) {
            try {
                $currentDayTime = $this->validatorHelper->getCurrentDateTime();
            } catch (\Exception $e) {
                return; // Unable to locate current date, so validation will not be performed
            }

            $day  = $this->validatorHelper->getSelectedDay($deliveryDateData);
            $diff = $currentDayTime->diff($day);

            if ($diff->invert && ($diff->days > 0 || ($diff->h * 60 + $diff->i) > 0)) {
                throw new ValidatorException(
                    __(
                        'Selected Delivery Date (%1) has already passed. Please select another Delivery Date and try again.',
                        $deliveryDateData->getDeliveryDay()
                    )
                );
            }
        }
    }
}
