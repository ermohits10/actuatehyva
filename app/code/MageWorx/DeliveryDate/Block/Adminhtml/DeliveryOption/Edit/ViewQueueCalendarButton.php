<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml\DeliveryOption\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ViewQueueCalendarButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonData(): array
    {
        if ($this->getEntityId()) {
            $data = [
                'label'      => __('View Queue Grid'),
                'on_click'   => sprintf("location.href = '%s';", $this->getQueueGridViewUrl()),
                'class'      => 'green_button',
                'sort_order' => 45
            ];
        } else {
            $data = [];
        }

        return $data;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQueueGridViewUrl(): string
    {
        return $this->getUrl('*/*/queue_grid', ['id' => $this->getEntityId()]);
    }
}
