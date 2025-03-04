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

class ValidateDayLimits implements \MageWorx\DeliveryDate\Api\DeliveryDateValidatorInterface
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
        $dayLimits = $deliveryOption->getDayLimits();
        if (empty($dayLimits)) {
            throw new ValidatorException(
                __(
                    'Selected Delivery Date is not available right now. Please select another Delivery Date and try again.'
                )
            );
        }

        $dayLimitIndexes        = array_keys($dayLimits);
        $firstAvailableDayIndex = reset($dayLimitIndexes);
        $lastAvailableDayIndex  = array_pop($dayLimitIndexes);

        $date = $deliveryDateData->getDeliveryDay();
        if ($date) {
            try {
                $currentDayTime = $this->validatorHelper->getCurrentDateTime();
            } catch (\Exception $e) {
                return; // Unable to locate current date, so validation will not be performed
            }

            $day  = $this->validatorHelper->getSelectedDay($deliveryDateData);
            $diff = $currentDayTime->diff($day);

            // Day is after start limit
            if (!$diff->invert && ($diff->days + 1) < $firstAvailableDayIndex) {
                throw new ValidatorException(
                    __(
                        'Selected Delivery Date is not available right now. Please select another Delivery Date and try again.'
                    )
                );
            }

            // Day is before last limit
            if (!$diff->invert && $diff->days > $lastAvailableDayIndex) {
                throw new ValidatorException(
                    __(
                        'Selected Delivery Date is not available right now. Please select another Delivery Date and try again.'
                    )
                );
            }

            $firstAvailableDayLimit = $dayLimits[$firstAvailableDayIndex];
            if ($date < $firstAvailableDayLimit['date']) {
                throw new ValidatorException(
                    __(
                        'Selected Delivery Date is not available right now. Please select another Delivery Date and try again.'
                    )
                );
            }
        }
    }
}
