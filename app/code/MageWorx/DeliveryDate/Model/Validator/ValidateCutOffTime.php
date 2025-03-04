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

class ValidateCutOffTime implements \MageWorx\DeliveryDate\Api\DeliveryDateValidatorInterface
{
    /**
     * @var ValidatorHelper
     */
    protected $validatorHelper;

    /**
     * @param ValidatorHelper $validatorHelper
     */
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
        if (!$deliveryOption->getCutOffTime()) {
            return; // Do not validate if cut off time is not set
        }

        $date = $deliveryDateData->getDeliveryDay();
        if ($date) {
            try {
                $currentDayTime = $this->validatorHelper->getCurrentDateTime();
            } catch (\Exception $e) {
                return; // Unable to locate current date, so validation will not be performed
            }

            $day  = $this->validatorHelper->getSelectedDay($deliveryDateData);

            if ((int)$currentDayTime->format('d') === (int)$day->format('d')) {
                $cutOffTime                = (string)$deliveryOption->getCutOffTime();
                $currentHours              = (int)$currentDayTime->format('H');
                $currentMinutes            = (int)$currentDayTime->format('i');
                $minutesFromMidnight       = $currentHours * 60 + $currentMinutes;
                $cutOffTimeParts           = explode(':', $cutOffTime);
                $cutOffMinutesFromMidnight = (int)$cutOffTimeParts[0] * 60
                    + (int)$cutOffTimeParts[1];

                if ($minutesFromMidnight > $cutOffMinutesFromMidnight) {
                    throw new ValidatorException(
                        __('Same day delivery is not available after %1', $cutOffTime)
                    );
                }
            }
        }
    }
}
