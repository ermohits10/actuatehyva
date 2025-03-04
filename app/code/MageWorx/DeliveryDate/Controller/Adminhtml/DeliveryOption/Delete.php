<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption
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
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('mageworx_deliverydate/deliveryoption/index');

        $id = $this->detectEntityId();
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Requested model no longer exists.'));

            return $resultRedirect;
        }

        try {
            $this->repository->deleteById($id);
            $this->messageManager->addSuccessMessage(
                __('The entity that had the id %1 was deleted successfully', [$id])
            );
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Requested entity no longer exists.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $resultRedirect;
    }
}
