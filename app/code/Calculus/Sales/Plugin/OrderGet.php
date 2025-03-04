<?php

namespace Calculus\Sales\Plugin;

class OrderGet
{
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getNcompassOrderCode($resultOrder);

        return $resultOrder;
    }

    private function getNcompassOrderCode(\Magento\Sales\Api\Data\OrderInterface $order)
    {

        try {
            
            $ncompassOrderCodeValue = $order->getNcompassOrderCode(); //This is not best practice, the attribute should be saved on a separate table and implement its own logic to load from the databse

        } catch (NoSuchEntityException $e) {
            return $order;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        //$ncompassOrderCode = $this->ncompassOrderCodeFactory->create();
        //$ncompassOrderCode->setValue($ncompassOrderCodeValue);
        $orderExtension->setNcompassOrderCode($ncompassOrderCodeValue);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }
    
    /*public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        Magento\Sales\Model\ResourceModel\Order\Collection $resultOrders
        ) {


    }*/
}