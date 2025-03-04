<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml\Order\Create;

class DeliveryInfo extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Generate form for editing of delivery date
     *
     * @param \Magento\Framework\DataObject $entity
     * @param string $entityType
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormHtml(\Magento\Framework\DataObject $entity, $entityType = 'quote')
    {
        return $this->getLayout()->createBlock(
            \MageWorx\DeliveryDate\Block\Adminhtml\Order\Create\DeliveryInfo\Form::class
        )->setEntity(
            $entity
        )->setEntityType(
            $entityType
        )->toHtml();
    }

    /**
     * Get block name
     *
     * @return string
     */
    public function getNameInLayout()
    {
        return $this->_nameInLayout ? $this->_nameInLayout : 'delivery_date_info';
    }
}
