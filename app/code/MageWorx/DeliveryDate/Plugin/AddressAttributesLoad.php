<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

use Magento\Quote\Api\Data\AddressExtensionInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressExtensionInterfaceFactory;

class AddressAttributesLoad
{
    /**
     * @var AddressExtensionInterfaceFactory
     */
    private $extensionFactory;

    /**
     * @param AddressExtensionInterfaceFactory $extensionFactory
     */
    public function __construct(AddressExtensionInterfaceFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Loads address entity extension attributes
     *
     * @param AddressInterface $entity
     * @param AddressExtensionInterface|null $extension
     * @return AddressExtensionInterface
     */
    public function afterGetExtensionAttributes(
        AddressInterface $entity,
        $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }
}
