<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class OnepageLayoutModifications implements LayoutProcessorInterface
{
    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    private $helper;

    /**
     * OnepageLayoutModifications constructor.
     *
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     */
    public function __construct(
        \MageWorx\DeliveryDate\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->helper->isEnabled()) {
            unset(
                $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]
                ["shippingAddress"]["children"]["shippingAdditional"]["children"]["delivery_date"]
            );
            unset(
                $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["store-pickup"]["children"]
                ["store-selector"]["children"]["delivery_date"]
            );

            unset($jsLayout["components"]["deliveryDateProvider"]);
            unset($jsLayout["components"]["ddConditionsListener"]);
            unset($jsLayout["components"]["ddDeliveryDataSaveUpdate"]);

            return $jsLayout;
        }

        // Magestore checkout compatibility
        if (isset($jsLayout["components"]["checkout"]["children"]["delivery-date"])) {
            $jsLayout["components"]["checkout"]["children"]["delivery-date"] =
                $jsLayout["components"]["checkout"]
                ["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]
                ["shippingAdditional"]["children"]["delivery_date"];

            $jsLayout["components"]["checkout"]["children"]["delivery-date"]["displayArea"] = 'delivery-date';
            unset(
                $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]
                ["shippingAddress"]["children"]["shippingAdditional"]["children"]["delivery_date"]
            );
        }

        return $jsLayout;
    }
}
