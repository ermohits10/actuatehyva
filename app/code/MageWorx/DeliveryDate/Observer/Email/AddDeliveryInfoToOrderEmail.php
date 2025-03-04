<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer\Email;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class AddDeliveryInfoToOrderEmail
 *
 * Add additional data to the order, invoice, shipment emails:
 * {{deliveryDate}}, {{deliveryTime}}, {{deliveryComment}}
 *
 */
class AddDeliveryInfoToOrderEmail implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    private $areaList;

    /**
     * AddDeliveryInfoToOrderEmail constructor.
     *
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \MageWorx\DeliveryDate\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\App\AreaList $areaList,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper       = $helper;
        $this->appEmulation = $appEmulation;
        $this->areaList     = $areaList;
        $this->logger       = $logger;
    }

    /**
     * @param Observer $observer
     * @return OrderInterface
     * @throws LocalizedException
     */
    protected function extractOrder(Observer $observer): OrderInterface
    {
        $transport = $observer->getData('transportObject');
        if (!$transport) {
            throw new LocalizedException(__('Unable to locate an order: transport is empty'));
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $transport->getData('order');
        if (!$order instanceof OrderInterface) {
            throw new LocalizedException(
                __(
                    'Unable to proceed: instance of the OrderInterface expected, got: %1',
                    get_class($order)
                )
            );
        }

        return $order;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $transport */
        $transport = $observer->getData('transportObject');
        if (!$transport) {
            return;
        }

        try {
            $order = $this->extractOrder($observer);
        } catch (LocalizedException $extractOrderException) {
            $this->logger->error($extractOrderException->getMessage());

            return;
        }

        if (!$order) {
            return;
        }

        if ($order->getIsVirtual()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Address $shippingAddress */
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            return;
        }

        $extensionAttributes = $shippingAddress->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            return;
        }

        $deliveryDay = $extensionAttributes->getDeliveryDay();
        if (!$deliveryDay) {
            return;
        }

        $this->appEmulation->startEnvironmentEmulation($order->getStoreId());

        try {
            $deliveryDayFormatted = $this->helper->formatDateFromDefaultToStoreSpecific(
                $deliveryDay,
                $order->getStoreId()
            );
            $deliveryDayMessage   = __(
                '%1 selected for the delivery.',
                [
                    $deliveryDayFormatted
                ]
            );

            $transport->setData('deliveryDate', $deliveryDayFormatted);

            $deliveryTimeArray = $this->helper->parseDeliveryDateArray($extensionAttributes);
            if ($deliveryTimeArray === null) {
                $deliveryTimeMessage = '';
            } else {
                $deliveryTimeMessage = $this->helper->getDeliveryTimeFormattedMessage(
                    $deliveryTimeArray['from']['full'],
                    $deliveryTimeArray['to']['full'],
                    $order->getStoreId()
                );
                $transport->setData('deliveryTime', $deliveryTimeMessage);
            }

            $deliveryComment = $extensionAttributes->getDeliveryComment();

            $transport->setData('deliveryComment', $deliveryComment);

            if ($this->helper->isDefaultDeliveryDateEmailOutputEnabled()) {
                $formattedShippingAddress = $transport->getData('formattedShippingAddress');
                $fullMessage              = '<br />' . $deliveryDayMessage .
                    '<br />' . $deliveryTimeMessage .
                    '<br />' . $deliveryComment .
                    '<br />';
                $transport->setData('formattedShippingAddress', $formattedShippingAddress . $fullMessage);
            }
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getMessage());

            return;
        } finally {
            $this->appEmulation->stopEnvironmentEmulation();
        }
    }
}
