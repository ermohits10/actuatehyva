<?php
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

interface QuoteAddressValidatorInterface
{
    /**
     * Validate delivery date and time in quote address
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     * @return void
     */
    public function validate(
        \Magento\Quote\Api\Data\CartInterface $quote,
        \Magento\Quote\Api\Data\AddressInterface $address
    ): void;
}
