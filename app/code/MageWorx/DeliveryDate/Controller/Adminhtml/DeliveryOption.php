<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface as EntityModelInterface;
use MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface as RepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class DeliveryOption extends Action
{
    const REGISTRY_ID           = 'mageworx_delivery_option_entity';
    const MENU_ID               = 'MageWorx_DeliveryDate::deliveryoption';
    const ID_REQUEST_PARAM      = 'id';

    /**
     * ACL Resource Key
     */
    const ADMIN_RESOURCE = 'MageWorx_DeliveryDate::delivery_option';

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DeliveryOption constructor.
     *
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param RepositoryInterface $repository
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        RepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->repository   = $repository;
        $this->logger       = $logger;
    }

    /**
     * Initiate action
     *
     * @return DeliveryOption
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(static::MENU_ID)
             ->_addBreadcrumb(
                 __('Delivery Options'),
                 __('Delivery Options')
             );

        return $this;
    }

    /**
     * Init delivery option entity using params from request
     *
     * @return EntityModelInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initModel()
    {
        $id = $this->detectEntityId();
        /** @var EntityModelInterface $model */
        if ($id) {
            $model = $this->repository->getById($id);
        } else {
            $model = $this->repository->getEmptyEntity();
        }

        $this->coreRegistry->register(static::REGISTRY_ID, $model);

        return $model;
    }

    /**
     * Detect entity id from request
     * Returns null when no id found
     *
     * @return int|null
     */
    protected function detectEntityId()
    {
        $id = $this->getRequest()->getParam(static::ID_REQUEST_PARAM) ?
            $this->getRequest()->getParam(static::ID_REQUEST_PARAM) :
            $this->getRequest()->getParam(EntityModelInterface::KEY_ID);

        if (!$id) {
            $general = $this->getRequest()->getParam('general');
            $id      = isset($general['entity_id']) ? $general['entity_id'] : null;
        }

        if ($id !== null) {
            $id = (int)$id;
        }

        return $id;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function redirectWhenNoModel()
    {
        $this->messageManager->addErrorMessage(__('Requested model no longer exists.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('mageworx_deliverydate/deliveryoption/index');

        return $resultRedirect;
    }
}
