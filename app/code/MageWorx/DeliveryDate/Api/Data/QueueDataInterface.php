<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api\Data;

/**
 * Interface QueueDataInterface
 *
 * Describes queue by each shipping method in magento
 * Stores the deliveries inside
 *
 * @method setDeliveryDay(string $value): QueueDataInterface
 * @method setDeliveryHoursFrom(string $value): QueueDataInterface
 * @method setDeliveryMinutesFrom(string $value): QueueDataInterface
 * @method setDeliveryHoursTo(string $value): QueueDataInterface
 * @method setDeliveryMinutesTo(string $value): QueueDataInterface
 * @method setDeliveryComment(string $value): QueueDataInterface
 * @method setDeliveryOption(int $value): QueueDataInterface
 * @method setDeliveryTime(string $value): QueueDataInterface
 */
interface QueueDataInterface extends DeliveryDateDataInterface
{
    const QUOTE_ADDRESS_ID_KEY    = 'quote_address_id';
    const ORDER_ADDRESS_ID_KEY    = 'order_address_id';
    const DELIVERY_TIME_FROM_KEY  = 'delivery_time_from';
    const DELIVERY_TIME_TO_KEY    = 'delivery_time_to';
    const SHIPPING_METHOD_KEY     = 'shipping_method';
    const CARRIER_KEY             = 'carrier';
    const STORE_ID_KEY            = 'store_id';

    const TABLE_NAME = 'mageworx_dd_queue';

    const TIME_DELIMITER = ':';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $id
     * @return QueueDataInterface
     */
    public function setEntityId($id);

    /**
     * @return int
     */
    public function getQuoteAddressId();

    /**
     * @param int $value
     * @return QueueDataInterface
     */
    public function setQuoteAddressId($value);

    /**
     * @return int
     */
    public function getOrderAddressId();

    /**
     * @param int $value
     * @return QueueDataInterface
     */
    public function setOrderAddressId($value);

    /**
     * @return string
     */
    public function getCarrier();

    /**
     * @param string $value
     * @return QueueDataInterface
     */
    public function setCarrier($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     * @return QueueDataInterface
     */
    public function setStoreId($value);
}
