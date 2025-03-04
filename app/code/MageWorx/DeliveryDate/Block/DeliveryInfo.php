<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\LocalizedException;

class DeliveryInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * DeliveryInfo constructor.
     *
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\DeliveryDate\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->helper   = $helper;
    }

    /**
     * Check is delivery date exist in the order
     *
     * @return bool
     */
    public function isDeliveryDateAvailable()
    {
        try {
            $order = $this->getOrder();
            if ($order->getIsVirtual()) {
                return false;
            }

            $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();
            if (!$shippingAddressExtensionAttributes->getDeliveryDay()
                && !$shippingAddressExtensionAttributes->getDeliveryHoursFrom()
                && !$shippingAddressExtensionAttributes->getDeliveryHoursTo()
            ) {
                return false;
            }

            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * Check is delivery date enabled in the store config
     *
     * @return bool
     */
    public function isDeliveryDateEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * Get extension attributes object assigned to the shipping address
     *
     * @return \Magento\Sales\Api\Data\OrderAddressExtensionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShippingAddressExtensionAttributes()
    {
        $order                              = $this->getOrder();
        $shippingAddress                    = $order->getShippingAddress();
        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        if ($shippingAddressExtensionAttributes === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to locate delivery date'));
        }

        return $shippingAddressExtensionAttributes;
    }

    /**
     * Returns complete delivery time message
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFullDeliveryMessage()
    {
        $deliveryDateMessage = $this->getDeliveryDateMessage();
        $deliveryTimeMessage = $this->getDeliveryTimeMessage();

        return $deliveryDateMessage . ' ' . $deliveryTimeMessage;
    }

    /**
     * Returns delivery date as a string
     *
     * @return \Magento\Framework\Phrase
     * @throws LocalizedException
     */
    public function getDeliveryDateMessage()
    {
        $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();
        $deliveryDate                       = $shippingAddressExtensionAttributes->getDeliveryDay();
        if ($deliveryDate) {
            $deliveryDateFormatted = $this->helper->formatDateFromDefaultToStoreSpecific(
                $deliveryDate,
                $this->getOrder()->getStoreId()
            );
            $deliveryDateMessage   = $deliveryDateFormatted;
        } else {
            $deliveryDateMessage = __('Delivery date was not set.');
        }

        return $deliveryDateMessage;
    }

    /**
     * Returns delivery time as a string
     *
     * @return \Magento\Framework\Phrase|string
     * @throws LocalizedException
     */
    public function getDeliveryTimeMessage()
    {
        $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();

        $deliveryTimeArray = $this->helper->parseDeliveryDateArray($shippingAddressExtensionAttributes);
        if ($deliveryTimeArray === null) {
            return '';
        }

        $deliveryTimeMessage = $this->helper->getDeliveryTimeFormattedMessage(
            $deliveryTimeArray['from']['full'],
            $deliveryTimeArray['to']['full'],
            $this->getOrder()->getStoreId()
        );

        return $deliveryTimeMessage;
    }

    /**
     * Returns delivery comment
     *
     * @return string
     * @throws LocalizedException
     */
    public function getDeliveryCommentMessage()
    {
        $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();
        $deliveryComment                    = $shippingAddressExtensionAttributes->getDeliveryComment();
        $result                             = '';
        if ($deliveryComment) {
            $result .= __('Comment: ') . $deliveryComment;
        }

        return $result;
    }

    /**
     * Is block editable
     *
     * @return bool
     */
    public function isEditable()
    {
        if (!$this->getData('is_editable')) {
            return false;
        }

        if (!$this->helper->getChangeDateByCustomerEnabled()) {
            return false;
        }

        if ($this->getOrder()->getIsVirtual()) {
            return false;
        }

        $shipments = $this->getOrder()->getShipmentsCollection();
        if ($shipments) {
            $shipments = $shipments->getTotalCount();
        }

        if ($shipments) {
            return false;
        }

        return true;
    }
}
