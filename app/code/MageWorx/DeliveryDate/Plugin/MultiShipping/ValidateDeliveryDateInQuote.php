<?php
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Plugin\MultiShipping;

use MageWorx\DeliveryDate\Api\QuoteAddressValidatorInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;

class ValidateDeliveryDateInQuote
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var QuoteAddressValidatorInterface
     */
    private $addressValidator;

    /**
     * ValidateDeliveryDateAvailability constructor.
     *
     * @param Helper $helper
     * @param QuoteAddressValidatorInterface $addressValidator
     */
    public function __construct(
        Helper $helper,
        QuoteAddressValidatorInterface $addressValidator
    ) {
        $this->helper           = $helper;
        $this->addressValidator = $addressValidator;
    }

    /**
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $subject
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $result
     * @param array $methods
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSetShippingMethods(
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $subject,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $result,
        array $methods
    ): \Magento\Multishipping\Model\Checkout\Type\Multishipping {
        if (!$this->helper->isEnabled()) {
            return $result;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subject->getQuote();
        if (!$quote || !$quote instanceof \Magento\Quote\Api\Data\CartInterface) {
            return $result;
        }

        $selectedMode = $this->helper->getQuoteLimitationMode($quote->getStoreId());
        if ($selectedMode === Helper::QUOTE_LIMITATION_MODE_OVERLOADING) {
            return $result;
        }

        if ($selectedMode === Helper::QUOTE_LIMITATION_MODE_RESTRICTION) {
            $shippingAddresses = $quote->getAllShippingAddresses();
            /** @var \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $shippingAddress */
            foreach ($shippingAddresses as $shippingAddress) {
                $this->addressValidator->validate($quote, $shippingAddress);
            }
        }

        return $result;
    }
}
