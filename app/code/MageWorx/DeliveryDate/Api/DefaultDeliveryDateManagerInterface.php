<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api;

/**
 * Interface DefaultDeliveryDateManagerInterface
 *
 * Responsible to set default delivery date to a quote
 */
interface DefaultDeliveryDateManagerInterface
{
    /**
     * Adds default delivery date to the quote if it could be found using selected shipping method
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function addToQuote(\Magento\Quote\Api\Data\CartInterface $quote): void;
}
