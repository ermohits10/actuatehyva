<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote as QuoteEntity;

/**
 * Class AddDefaultDeliveryDate
 *
 * Adds default delivery date to the quote if enabled
 */
class AddDefaultDeliveryDate
{
    /**
     * @var \MageWorx\DeliveryDate\Api\DefaultDeliveryDateManagerInterface
     */
    private $defaultDeliveryDateManager;

    /**
     * AddDefaultDeliveryDate constructor.
     *
     * @param \MageWorx\DeliveryDate\Api\DefaultDeliveryDateManagerInterface $defaultDeliveryDateManager
     */
    public function __construct(
        \MageWorx\DeliveryDate\Api\DefaultDeliveryDateManagerInterface $defaultDeliveryDateManager
    ) {
        $this->defaultDeliveryDateManager = $defaultDeliveryDateManager;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param QuoteEntity $quote
     * @param array $orderData
     */
    public function beforeSubmit(\Magento\Quote\Model\QuoteManagement $subject, QuoteEntity $quote, $orderData = [])
    {
        $this->defaultDeliveryDateManager->addToQuote($quote);
    }
}
