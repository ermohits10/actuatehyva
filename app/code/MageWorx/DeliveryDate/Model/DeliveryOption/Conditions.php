<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\DeliveryOption;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryOptionConditionsDataInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionConditionsInterface;

class Conditions extends DataObject implements DeliveryOptionConditionsInterface
{
    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartInterfaceFactory
     */
    protected $cartFactory;

    /**
     * @var \Magento\Quote\Api\GuestCartRepositoryInterface
     */
    protected $guestCartRepository;

    /**
     * @param AddressInterfaceFactory $addressFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory
     * @param array $data
     */
    public function __construct(
        AddressInterfaceFactory                         $addressFactory,
        \Magento\Quote\Api\CartRepositoryInterface      $cartRepository,
        \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepository,
        \Magento\Quote\Api\Data\CartInterfaceFactory    $cartFactory,
        array                                           $data = []
    ) {
        $this->addressFactory      = $addressFactory;
        $this->cartRepository      = $cartRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->cartFactory         = $cartFactory;
        parent::__construct($data);
    }

    /**
     * Shipping method condition
     *
     * @return string|null
     */
    public function getShippingMethod(): ?string
    {
        $method = (string)$this->getData('shipping_method');
        if ($method === 'instore_pickup') {
            $method = 'instore_instore';
        }

        return $method;
    }

    /**
     * @param string|null $value
     * @return \MageWorx\DeliveryDate\Api\Data\DeliveryOptionConditionsDataInterface
     */
    public function setShippingMethod(
        ?string $value
    ): \MageWorx\DeliveryDate\Api\Data\DeliveryOptionConditionsDataInterface {
        return $this->setData('shipping_method', $value);
    }

    /**
     * Quote id condition
     *
     * @return int
     */
    public function getCartId(): int
    {
        return (int)$this->getData('cart_id');
    }

    /**
     * Quote id condition
     *
     * @param int $value
     * @return \MageWorx\DeliveryDate\Api\Data\DeliveryOptionConditionsDataInterface
     */
    public function setCartId(int $value): \MageWorx\DeliveryDate\Api\Data\DeliveryOptionConditionsDataInterface
    {
        return $this->setData('cart_id', $value);
    }

    /**
     * @return AddressInterface
     */
    public function getAddress(): AddressInterface
    {
        $address = $this->getData('address');

        if ($address === null) {
            // Return empty address if null
            $address = $this->addressFactory->create();
        }

        return $address;
    }

    /**
     * @param AddressInterface $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setAddress(AddressInterface $value): DeliveryOptionConditionsDataInterface
    {
        return $this->setData('address', $value);
    }

    /**
     * @return CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote(): CartInterface
    {
        $cartId      = $this->getCartId();
        $guestCartId = $this->getGuestCartId();

        if ($cartId) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->get($cartId);
        } elseif ($guestCartId) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->guestCartRepository->get($guestCartId);
        } else {
            $quote = $this->cartFactory->create();
        }

        return $quote;
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        return $this->getData('store_id');
    }

    /**
     * @param int $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setStoreId(int $value): DeliveryOptionConditionsDataInterface
    {
        return $this->setData('store_id', $value);
    }

    /**
     * @return int|null
     */
    public function getCustomerGroupId(): ?int
    {
        return $this->getData('customer_group_id');
    }

    /**
     * @param int $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setCustomerGroupId(int $value): DeliveryOptionConditionsDataInterface
    {
        return $this->setData('customer_group_id', $value);
    }

    /**
     * Quote id condition for guest customer
     *
     * @return string|null
     */
    public function getGuestCartId(): ?string
    {
        return $this->getData('guest_cart_id');
    }

    /**
     * Quote id condition for guest customer
     *
     * @param string|null $value
     * @return DeliveryOptionConditionsDataInterface
     */
    public function setGuestCartId(?string $value): DeliveryOptionConditionsDataInterface
    {
        return $this->setData('guest_cart_id', $value);
    }
}
