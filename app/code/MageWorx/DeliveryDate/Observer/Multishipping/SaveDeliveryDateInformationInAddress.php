<?php

namespace MageWorx\DeliveryDate\Observer\Multishipping;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class SaveDeliveryDateInformationInAddress implements ObserverInterface
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getData('request');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');

        $postDeliveryData = $request->getPost('delivery_date');
        if (empty($postDeliveryData)) {
            return; // There was no data provided
        }

        foreach ($postDeliveryData as $addressId => $deliveryData) {
            /** @var \Magento\Quote\Model\Quote\Address $quoteAddresses */
            $quoteAddresses = $quote->getAddressById($addressId);
            /** @var \Magento\Quote\Api\Data\AddressExtension $extension */
            $extension = $quoteAddresses->getExtensionAttributes();

            $extension->setQuoteAddressId($addressId);
            if (!empty($deliveryData[QueueDataInterface::DELIVERY_DAY_KEY])) {
                $extension->setDeliveryDay($deliveryData[QueueDataInterface::DELIVERY_DAY_KEY]);
            } else {
                return; // We cant process delivery date without date
            }

            if (!empty($deliveryData['delivery_option_id'])) {
                $extension->setDeliveryOptionId($deliveryData['delivery_option_id']);
            }

            if (isset($deliveryData[QueueDataInterface::DELIVERY_COMMENT_KEY])) {
                $extension->setDeliveryComment($deliveryData[QueueDataInterface::DELIVERY_COMMENT_KEY]);
            }

            if (isset($deliveryData[QueueDataInterface::DELIVERY_TIME_KEY])) {
                $deliveryTime = $deliveryData[QueueDataInterface::DELIVERY_TIME_KEY];
                $extension->setDeliveryTime($deliveryTime);

                if (!empty($deliveryTime)) {
                    $timeParts = explode('_', $deliveryTime);
                    $timeFrom  = $timeParts[0];
                    $timeTo    = $timeParts[1];

                    [$hoursFrom, $minutesFrom] = explode(QueueDataInterface::TIME_DELIMITER, $timeFrom);
                    [$hoursTo, $minutesTo] = explode(QueueDataInterface::TIME_DELIMITER, $timeTo);
                } else {
                    $hoursFrom   = null;
                    $minutesFrom = null;
                    $hoursTo     = null;
                    $minutesTo   = null;
                }

                $extension->setDeliveryHoursFrom($hoursFrom);
                $extension->setDeliveryMinutesFrom($minutesFrom);
                $extension->setDeliveryHoursTo($hoursTo);
                $extension->setDeliveryMinutesTo($minutesTo);
            }
        }
    }
}
