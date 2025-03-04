<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml\Queue;

class Calendar extends \Magento\Backend\Block\Template
{
    /**
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->getData('store_id');
        if ($storeId === null) {
            $storeId = $this->getParentBlock()->getStoreId();
        }

        return (int)$storeId;
    }

    /**
     * @return int
     */
    public function getQueueId()
    {
        $queueId = $this->getData('id');
        if ($queueId === null) {
            $queueId = $this->getParentBlock()->getQueueId();
        }

        return (int)$queueId;
    }

    /**
     * @return bool
     */
    public function getUseMenu()
    {
        $useMenu = $this->getData('use_menu');
        if ($useMenu === null) {
            $useMenu = true;
        }

        return (bool)$useMenu;
    }
}
