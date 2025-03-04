<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

class AddDeliveryDateForm
{
    const MAX_ITERATIONS = 2;

    private $iterator = 0;

    /**
     * Add delivery option selection form to the admin order creation page
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Data $subject
     * @param $result
     * @param string $alias
     * @param bool $useCache
     * @return string
     */
    public function afterGetChildHtml(
        \Magento\Sales\Block\Adminhtml\Order\Create\Data $subject,
        $result,
        $alias = ''
    ) {
        if ($alias === 'gift_options' && $this->iterator < self::MAX_ITERATIONS) {
            $this->iterator++;
            $result .= $subject->getChildHtml('delivery_date_info');
        }

        return $result;
    }
}
