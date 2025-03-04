<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassStatus extends Action
{
    const ADMIN_RESOURCE = 'MageWorx_DeliveryDate::delivery_option';

    const STATUS_ENABLED  = '1';
    const STATUS_DISABLED = '0';

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
     * @var string
     */
    private $activeFieldName;

    /**
     * @var string
     */
    private $activeRequestParamName;

    /**
     * @var \MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface
     */
    private $repository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\CollectionFactory $collectionFactory
     * @param \MageWorx\DeliveryDate\Api\DeliveryOptionInterfaceFactory $entityFactory
     * @param string $activeFieldName
     * @param string $activeRequestParamName
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\CollectionFactory $collectionFactory,
        \MageWorx\DeliveryDate\Api\DeliveryOptionInterfaceFactory $entityFactory,
        \MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface $repository,
        $activeFieldName = \MageWorx\DeliveryDate\Api\DeliveryOptionInterface::KEY_IS_ACTIVE,
        $activeRequestParamName = 'status'
    ) {
        parent::__construct($context);
        $this->filter                 = $filter;
        $this->collectionFactory      = $collectionFactory;
        $this->entityFactory          = $entityFactory;
        $this->repository             = $repository;
        $this->activeFieldName        = $activeFieldName;
        $this->activeRequestParamName = $activeRequestParamName;
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
            $updatedCount = 0;

            switch ($this->getRequest()->getParam($this->activeRequestParamName)) {
                case static::STATUS_ENABLED:
                    $active = 1;
                    break;
                case static::STATUS_DISABLED:
                    $active = 0;
                    break;
                default:
                    $active = 1;
            }

            foreach ($collection->getAllIds() as $entityId) {
                try {
                    /** @var \Magento\Framework\Model\AbstractModel $entity */
                    $entity = $this->repository->getById($entityId);
                    $entity->setData($this->activeFieldName, $active);
                    $this->repository->save($entity);
                    $updatedCount++;
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    continue;
                }
            }

            if ($updatedCount) {
                $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $updatedCount));
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
