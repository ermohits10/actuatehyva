<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

use MageWorx\DeliveryDate\Api\Data\DeliveryOptionConditionsDataInterface;

interface DeliveryOptionConditionsInterface extends DeliveryOptionConditionsDataInterface
{
    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote(): \Magento\Quote\Api\Data\CartInterface;
}
