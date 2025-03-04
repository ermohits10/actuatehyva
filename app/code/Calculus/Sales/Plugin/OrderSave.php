<?php

namespace Calculus\Sales\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;

class OrderSave

{
    protected $_logger;
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->saveNcompassOrderCode($resultOrder);

        return $resultOrder;
    }

    private function saveNcompassOrderCode(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (
            null !== $extensionAttributes &&
            null !== $extensionAttributes->getNcompassOrderCode()
        ) {
            $ncompassOrderCodeValue = $extensionAttributes->getNcompassOrderCode();
            //$ncompassordercode = $order->getNcompassOrderCode();
            //$this->_logger->info("nc value ".$ncompassordercode);
            try {
                $order->setNcompassOrderCode($ncompassOrderCodeValue);
                //$order->setNcompassOrderCode($ncompassOrderCodeValue);
                
                //$order->setNcompassOrderCode("-999");
                $order->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __('Could not add attribute to order: "%1"', $e->getMessage()),
                    $e
                );
            }
        }
        return $order;
    }
}