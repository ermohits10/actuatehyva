<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

use MageWorx\DeliveryDate\Api\Data\LimitsInterface;

/**
 * Convert day\time limits from general array to array of objects.
 */
interface DeliveryLimitsConverterInterface
{
    /**
     * @param array $limitsArray
     * @return LimitsInterface[]
     */
    public function convertToObjectArray(array $limitsArray): array;
}
