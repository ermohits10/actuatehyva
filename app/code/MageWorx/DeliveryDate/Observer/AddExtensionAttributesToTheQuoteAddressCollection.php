<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class AddExtensionAttributesToTheQuoteAddressCollection implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Address\Collection $collection */
        $collection = $observer->getData('quote_address_collection');
        if (!$collection instanceof \Magento\Quote\Model\ResourceModel\Quote\Address\Collection) {
            return;
        }

        if ($collection->isLoaded()) {
            return;
        }

        $joinTable = $collection->getTable(QueueDataInterface::TABLE_NAME);
        $collection->getSelect()
                   ->joinLeft(
                       $joinTable,
                       '`main_table`.`address_id` = `' . $joinTable . '`.`quote_address_id`',
                       [
                           QueueDataInterface::DELIVERY_DAY_KEY,
                           QueueDataInterface::DELIVERY_HOURS_FROM_KEY,
                           QueueDataInterface::DELIVERY_MINUTES_FROM_KEY,
                           QueueDataInterface::DELIVERY_HOURS_TO_KEY,
                           QueueDataInterface::DELIVERY_MINUTES_TO_KEY,
                           QueueDataInterface::DELIVERY_COMMENT_KEY,
                           'delivery_option_id' => QueueDataInterface::DELIVERY_OPTION_ID_KEY,
                           'quote_address_id' => QueueDataInterface::QUOTE_ADDRESS_ID_KEY,
                       ]
                   );
    }
}
