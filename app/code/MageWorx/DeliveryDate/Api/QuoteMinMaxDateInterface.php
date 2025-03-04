<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

use MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface;

/**
 * Interface QuoteMinMaxDateInterface
 *
 * Calculate available delivery date diapason for the quote
 */
interface QuoteMinMaxDateInterface
{
    const MW_DELIVERY_DATE_AVAILABLE_FROM = 'mw_delivery_date_available_from';
    const MW_DELIVERY_DATE_AVAILABLE_TO = 'mw_delivery_date_available_to';

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return QuoteMinMaxDateInterface
     */
    public function setQuote(\Magento\Quote\Api\Data\CartInterface $quote): QuoteMinMaxDateInterface;

    /**
     * Get actual quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuote(): ?\Magento\Quote\Api\Data\CartInterface;

    /**
     * @return DateDiapasonInterface
     */
    public function getDiapason(): DateDiapasonInterface;
}
