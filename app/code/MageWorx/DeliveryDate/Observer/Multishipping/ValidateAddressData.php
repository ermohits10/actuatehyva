<?php

namespace MageWorx\DeliveryDate\Observer\Multishipping;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\QueueManagerInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;

/**
 * Class ValidateAddressData
 *
 * @package MageWorx\DeliveryDate\Observer\Multishipping
 * @TODO DELETE IT
 */
class ValidateAddressData implements ObserverInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var \MageWorx\DeliveryDate\Api\QuoteAddressValidatorInterface
     */
    private $addressValidator;

    /**
     * ValidateDeliveryDateAvailability constructor.
     *
     * @param Helper $helper
     * @param TimezoneInterface $timezone
     * @param QueueManagerInterface $queueManager
     * @param DeliveryManagerInterface $deliveryManager
     */
    public function __construct(
        Helper $helper,
        \MageWorx\DeliveryDate\Api\QuoteAddressValidatorInterface $addressValidator
    ) {
        $this->helper           = $helper;
        $this->addressValidator = $addressValidator;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        if (!$quote || !$quote instanceof \Magento\Quote\Api\Data\CartInterface) {
            return;
        }

        $selectedMode = $this->helper->getQuoteLimitationMode($quote->getStoreId());
        if ($selectedMode === Helper::QUOTE_LIMITATION_MODE_OVERLOADING) {
            return;
        }

        if ($selectedMode === Helper::QUOTE_LIMITATION_MODE_RESTRICTION) {
            $shippingAddresses = $quote->getAllShippingAddresses();
            /** @var \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $shippingAddress */
            foreach ($shippingAddresses as $shippingAddress) {
                $this->addressValidator->validate($quote, $shippingAddress);
            }
        }
    }
}
