<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

class AddressExtensionAttributesMerger
{
    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $subject
     * @param callable $proceed
     * @param array $data
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function aroundAddData(
        \Magento\Quote\Api\Data\AddressInterface $subject,
        callable $proceed,
        array $data
    ) {
        $extensionAttributesBefore = $subject->getExtensionAttributes();
        /** @var \Magento\Quote\Api\Data\AddressInterface $result */
        $result = $proceed($data);
        $extensionAttributesAfter = $result->getExtensionAttributes();
        if (method_exists($extensionAttributesBefore, 'getPickupLocationCode') &&
            $extensionAttributesBefore->getPickupLocationCode() &&
            !$extensionAttributesAfter->getPickupLocationCode()
        ) {
            $extensionAttributesAfter->setPickupLocationCode($extensionAttributesBefore->getPickupLocationCode());
        }

        return $result;
    }
}
