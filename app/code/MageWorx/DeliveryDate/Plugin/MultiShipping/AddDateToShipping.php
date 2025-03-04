<?php

namespace MageWorx\DeliveryDate\Plugin\MultiShipping;

class AddDateToShipping
{
    /**
     * @param \Magento\Multishipping\Block\Checkout\Shipping $subject
     * @param string $result
     * @param \Magento\Framework\DataObject $addressEntity
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetItemsBoxTextAfter(
        \Magento\Multishipping\Block\Checkout\Shipping $subject,
        string $result,
        \Magento\Framework\DataObject $addressEntity
    ) {
        //$groups = $addressEntity->getGroupedAllShippingRates();
        $deliveryDateBlock = $subject->getLayout()->createBlock(
            \MageWorx\DeliveryDate\Block\MultiShipping\DeliveryDate::class,
            'delivery-date-' . $addressEntity->getId(),
            [
                'data' => [
                    'entity' => $addressEntity
                ]
            ]
        );

        return $deliveryDateBlock->toHtml() . $result;
    }
}
