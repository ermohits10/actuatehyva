<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\CartInterface;

class AddDefaultDeliveryDate implements ObserverInterface
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
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $this->locateQuote($observer);
        if (!$quote) {
            return;
        }

        if (!$quote instanceof CartInterface) {
            return;
        }

        $this->defaultDeliveryDateManager->addToQuote($quote);
    }

    /**
     * Detect quote in tne event object of the observer
     *
     * @param Observer $observer
     * @return CartInterface|null
     */
    public function locateQuote(Observer $observer): ?CartInterface
    {
        // Frontend
        $quote = $observer->getEvent()->getData('quote');

        return $quote;
    }
}
