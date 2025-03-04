<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption\Queue;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;

class Grid extends \MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption
{
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Queue %1', $this->_request->getParam('id')));
        $resultPage->setActiveMenu(static::MENU_ID);

        return $resultPage;
    }
}
