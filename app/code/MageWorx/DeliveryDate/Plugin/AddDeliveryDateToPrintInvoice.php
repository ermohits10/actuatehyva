<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Plugin;

use Magento\Sales\Api\Data\OrderInterface;

class AddDeliveryDateToPrintInvoice
{
    /**
     * @var bool
     */
    protected $isPrintingInvoiceFlag = false;

    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     */
    public function __construct(
        \MageWorx\DeliveryDate\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param $subject
     * @param ...$arguments
     * @return array
     */
    public function beforeGetPdf($subject, ...$arguments): array
    {
        $this->isPrintingInvoiceFlag = true;

        return $arguments;
    }

    /**
     * @param $subject
     * @param \Zend_Pdf $result
     * @return \Zend_Pdf
     */
    public function afterGetPdf($subject, \Zend_Pdf $result): \Zend_Pdf
    {
        $this->isPrintingInvoiceFlag = false;

        return $result;
    }

    /**
     * Adds delivery information to shipping description of the order when printing invoices pdf
     *
     * @param OrderInterface $subject
     * @param string|null $result
     * @return string
     */
    public function afterGetShippingDescription(OrderInterface $subject, ?string $result): ?string
    {
        if ($this->isPrintingInvoiceFlag !== true) {
            return $result;
        }

        /** @var \Magento\Sales\Model\Order $subject */
        if (!$subject->getIsVirtual() && $subject->getShippingAddress()) {
            /** @var \Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Sales\Model\Order\Address $shippingAddress */
            $shippingAddress = $subject->getShippingAddress();
            if ($shippingAddress->getExtensionAttributes() === null) {
                return $result;
            }

            $deliveryDay = $shippingAddress->getExtensionAttributes()->getDeliveryDay();
            $deliveryTime = $shippingAddress->getExtensionAttributes()->getDeliveryTime();
            if ($deliveryDay) {
                $deliveryMessage = ' ' . str_repeat('_', 45);
                $deliveryMessage .= __('Delivery Date:') . ' ' . $this->helper->formatDateFromDefaultToStoreSpecific(
                    $deliveryDay,
                    (int)$subject->getStoreId()
                );
                if ($deliveryTime) {
                    $deliveryTimeParsed = $this->helper->parseDeliveryDateArray($shippingAddress->getExtensionAttributes());
                    if ($deliveryTimeParsed !== null) {
                        $deliveryMessage .= ' ' . str_repeat('_', 45);
                        $deliveryMessage .= ' ' . __('Delivery Time:') . ' ' . $this->helper->getDeliveryTimeFormattedMessage(
                            $deliveryTimeParsed['from']['full'],
                            $deliveryTimeParsed['to']['full'],
                            (int)$subject->getStoreId()
                        );
                    }
                }
                if($deliveryCommetn){
                    $deliveryMessage .= ' ' . str_repeat('_', 45);
                    $deliveryMessage .= ' ' . __('Delivery Comment:') . ' ' . $deliveryCommetn;
                }

                $result .= ' ' . $deliveryMessage;
            }
        }

        return $result;
    }
}
