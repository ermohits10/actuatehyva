<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

use Magento\Framework\Api\ExtensibleDataInterface;

class CoreBugFixWithExtensionAttributes
{
    /**
     * Fix for the core magento 2.2 bug described in:
     *
     * @link https://github.com/magento/magento2/issues/12655
     * @source https://github.com/magento/magento2/commit/aa535e
     *
     * @param $subject
     * @param $dataObject
     * @param array $data
     * @param $interfaceName
     * @return array
     */
    public function beforePopulateWithArray($subject, $dataObject, array $data, $interfaceName)
    {
        if ($dataObject instanceof \Magento\Quote\Api\Data\TotalsInterface &&
            $interfaceName === \Magento\Quote\Api\Data\TotalsInterface::class &&
            !empty($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])
        ) {
            unset($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        }

        return [$dataObject, $data, $interfaceName];
    }
}
