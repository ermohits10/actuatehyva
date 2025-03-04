<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface as RepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Save extends \MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption
{
    const ALL_STORE_VIEWS = '0';

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param RepositoryInterface $repository
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        RepositoryInterface $repository,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context, $coreRegistry, $repository, $logger);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->timezone          = $timezone;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            return $this->_redirect('*/*/');
        }

        $id = $this->detectEntityId();

        try {
            $model        = $this->initModel();
            $data         = $this->getRequest()->getPostValue();
            $data         = $this->prepareData($data, $model);
            $dataAsObject = $this->dataObjectFactory->create(['data' => $data]);

            $validateResult = $model->validateData($dataAsObject);
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);

                return $this->_redirect('*/*/edit', ['id' => $model->getId()]);
            }

            $model->addData($data);
            $this->_session->setPageData($model->getData());

            $this->repository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the delivery option.'));
            $this->_session->setPageData(false);

            if ($this->getRequest()->getParam('back') == 'newAction') {
                return $this->_redirect('*/*/create');
            }

            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('*/*/edit', ['id' => $model->getId()]);
            }

            return $this->_redirect('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            if (!empty($id)) {
                return $this->_redirect('*/*/edit', ['id' => $id]);
            } else {
                return $this->_redirect('*/*/create');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the delivery option data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);

            return $this->_redirect('*/*/edit', ['id' => $id]);
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @param \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $model
     * @return array
     */
    protected function prepareData($data, \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $model)
    {
        // Save store specific messages in general tab.
        if (!empty($data['delivery_date_required_error_message'])) {
            $data['general']['delivery_date_required_error_message'] = $data['delivery_date_required_error_message'];
        }

        $data = $data['general'];

        if (!isset($data['entity_id']) || !$data['entity_id']) {
            $data['entity_id'] = null;
        }

        if (empty($data['store_ids'])) {
            $data['store_ids'] = [static::ALL_STORE_VIEWS];
        } else {
            $storeIdsOrig = $model->getStoreIds();
            if ($storeIdsOrig &&
                in_array(static::ALL_STORE_VIEWS, $data['store_ids']) &&
                !in_array(static::ALL_STORE_VIEWS, $storeIdsOrig)
            ) {
                $data['store_ids'] = [static::ALL_STORE_VIEWS];
            } elseif (count($data['store_ids']) > 1) {
                $data['store_ids'] = array_filter($data['store_ids']);
            }
        }

        if (empty($data['customer_group_ids'])) {
            $data['customer_group_ids'] = null;
        }

        if (empty($data['holidays_serialized'])) {
            $data['holidays_serialized'] = [];
        }

        if (!empty($data['active_from'])) {
            $data['active_from'] = $this->timezone->date($data['active_from']);
        }

        if (!empty($data['active_to'])) {
            $data['active_to'] = $this->timezone->date($data['active_to']);
        }

        return $data;
    }
}
