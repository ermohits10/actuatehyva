<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Controller\Adminhtml\PriceOptions;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    protected $priceOptions;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \AutifyDigital\V12Finance\Model\PriceOptions $priceOptions
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->priceOptions = $priceOptions;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('priceoptions_id');

            $model = $this->priceOptions->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Price Options no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            // $model->setData($data);
            $financeOptions = implode(",", $data['finance_options']);
            $model->setPriceFrom($data['price_from']);
            $model->setPriceTo($data['price_to']);
            $model->setFinanceOptions($financeOptions);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Price Options.'));
                $this->dataPersistor->clear('autifydigital_v12finance_priceoptions');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['priceoptions_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Price Options.'));
            }

            $this->dataPersistor->set('autifydigital_v12finance_priceoptions', $data);
            return $resultRedirect->setPath('*/*/edit', ['priceoptions_id' => $this->getRequest()->getParam('priceoptions_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('AutifyDigital_V12Finance::PriceOptions_save');
    }
}

