<?php
/**
 * Copyright MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    const QUEUE_MAIN_BLOCK_NAME = 'queue_content';
    const ALL_STORES            = 0;

    /**
     * Load the page
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        // @TODO For future version
//        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
//
//        try {
//            $this->prepareQueueBlock($resultPage);
//        } catch (\MageWorx\DeliveryDate\Exceptions\QueueException $queueException) {
//            $this->messageManager->addErrorMessage(__('Unable to load the Queue block'));
//            return $this->_redirect('index');
//        }
//
//        return $resultPage;

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('MageWorx_DeliveryDate::queue');
        $resultPage->getConfig()->getTitle()->prepend(__('Queue'));

        return $resultPage;
    }

    /**
     * @TODO For future version
     *
     * @param \Magento\Framework\View\Result\Page $page
     * @return \MageWorx\DeliveryDate\Block\Adminhtml\Queue
     * @throws \MageWorx\DeliveryDate\Exceptions\QueueException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareQueueBlock(\Magento\Framework\View\Result\Page $page)
    {
        /** @var \MageWorx\DeliveryDate\Block\Adminhtml\Queue $queueBlock */
        $queueBlock = $page->getLayout()->getBlock(static::QUEUE_MAIN_BLOCK_NAME);
        if (!$queueBlock instanceof \MageWorx\DeliveryDate\Block\Adminhtml\Queue) {
            throw new \MageWorx\DeliveryDate\Exceptions\QueueException(
                __(
                    'Queue block should be instance of \MageWorx\DeliveryDate\Block\Adminhtml\Queue'
                )
            );
        }

        /** @var \Magento\Backend\Block\Store\Switcher $storeSwitcherBlock */
        $storeSwitcherBlock = $page->getLayout()->getBlock('store_switcher');
        if (!$storeSwitcherBlock instanceof \Magento\Backend\Block\Store\Switcher) {
            throw new \MageWorx\DeliveryDate\Exceptions\QueueException(
                __(
                    'Store Switcher block should be instance of \Magento\Backend\Block\Store\Switcher , %1 given',
                    get_class($storeSwitcherBlock)
                )
            );
        }

        $storeVarName = $storeSwitcherBlock->getStoreVarName();
        $storeView    = $this->getRequest()->getParam($storeVarName);
        if (!$storeView) {
            $storeView = static::ALL_STORES;
        }

        $queueBlock->setStoreId($storeView);

        return $queueBlock;
    }
}
