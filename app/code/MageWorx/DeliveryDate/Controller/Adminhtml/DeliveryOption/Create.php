<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Create extends \MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption
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
        /** @var \Magento\Framework\Controller\Result\Forward $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $result->forward('edit');

        return $result;
    }
}
