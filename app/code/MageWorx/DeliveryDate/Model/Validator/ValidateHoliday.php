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

class ValidateHoliday implements \MageWorx\DeliveryDate\Api\DeliveryDateValidatorInterface
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
            $day  = $this->validatorHelper->getSelectedDay($deliveryDateData);
            $holiday = $deliveryOption->isDayHoliday($day);
            if ($holiday) {
                throw new ValidatorException(
                    __(
                        'Sorry, selected date is not available because it is holiday, please select another delivery date.'
                    )
                );
            }
        }
    }
}
