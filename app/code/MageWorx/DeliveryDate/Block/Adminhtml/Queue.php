<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml;

use Magento\Framework\Exception\LocalizedException;

class Queue extends \Magento\Backend\Block\Template
{
    /**
     * @return int
     */
    public function getStoreId()
    {
        return (int)$this->getData('store_id');
    }

    /**
     * @return int
     */
    public function getQueueId()
    {
        return (int)$this->_request->getParam('id');
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getBackButton()
    {
        $buttonsBlock = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class,
            'back_button',
            [
                'data' => [
                    'label'    => __('Back to the Delivery Option'),
                    'title'    => __('Back'),
                    'on_click' => 'document.location = \'' . $this->getBackUrl() . '\'',
                    'class'    => 'back_button_main action- scalable back'
                ]
            ]
        );

        return $buttonsBlock->toHtml();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        $deliveryOptionId = $this->_request->getParam('id');
        $url              = $this->_urlBuilder->getUrl('*/*/edit', ['id' => $deliveryOptionId]);

        return $url;
    }
}
