<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'MageWorx_DeliveryDate::delivery_option';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var string
     */
    private $redirectUrl = '*/*/index';

    /**
     * @var mixed
     */
    private $collectionFactory;

    /**
     * @var mixed
     */
    private $entityFactory;

    /**
     * @var \MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface
     */
    private $repository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\CollectionFactory $collectionFactory
     * @param \MageWorx\DeliveryDate\Api\DeliveryOptionInterfaceFactory $entityFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\CollectionFactory $collectionFactory,
        \MageWorx\DeliveryDate\Api\DeliveryOptionInterfaceFactory $entityFactory,
        \MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->entityFactory     = $entityFactory;
        $this->repository        = $repository;
    }

    /**
     * Update is active status
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection   = $this->filter->getCollection($this->collectionFactory->create());
            $deletedCount = 0;
            foreach ($collection->getAllIds() as $entityId) {
                $this->repository->deleteById($entityId);
                $deletedCount++;
            }

            if ($deletedCount) {
                $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $deletedCount));
            }

            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath($this->redirectUrl);

            return $resultRedirect;
        } catch (\Exception $e) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
