<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api\Data;

use Magento\Quote\Api\Data\AddressInterface;

interface DeliveryDateDataInterface
{
    const DELIVERY_DAY_KEY          = 'delivery_day';
    const DELIVERY_HOURS_FROM_KEY   = 'delivery_hours_from';
    const DELIVERY_MINUTES_FROM_KEY = 'delivery_minutes_from';
    const DELIVERY_HOURS_TO_KEY     = 'delivery_hours_to';
    const DELIVERY_MINUTES_TO_KEY   = 'delivery_minutes_to';
    const DELIVERY_COMMENT_KEY      = 'delivery_comment';
    const DELIVERY_TIME_KEY         = 'delivery_time';
    const DELIVERY_OPTION_ID_KEY    = 'delivery_option';
    const SELECTED_SHIPPING_METHOD  = 'shipping_method';

    /**
     * @return string
     */
    public function getDeliveryDay(): string;

    /**
     * @param string $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryDay(string $value): DeliveryDateDataInterface;

    /**
     * @return int|null
     */
    public function getDeliveryHoursFrom(): ?int;

    /**
     * @param int|null $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryHoursFrom(?int $value): DeliveryDateDataInterface;

    /**
     * @return int|null
     */
    public function getDeliveryMinutesFrom(): ?int;

    /**
     * @param int|null $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryMinutesFrom(?int $value): DeliveryDateDataInterface;

    /**
     * @return int|null
     */
    public function getDeliveryHoursTo(): ?int;

    /**
     * @param int|null $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryHoursTo(?int $value): DeliveryDateDataInterface;

    /**
     * @return int|null
     */
    public function getDeliveryMinutesTo(): ?int;

    /**
     * @param int|null $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryMinutesTo(?int $value): DeliveryDateDataInterface;

    /**
     * @return string|null
     */
    public function getDeliveryComment(): ?string;

    /**
     * @param string|null $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryComment(?string $value): DeliveryDateDataInterface;

    /**
     * @return string
     */
    public function getDeliveryTime(): ?string;

    /**
     * @param string|null $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryTime(?string $value): DeliveryDateDataInterface;

    /**
     * @return int
     */
    public function getDeliveryOption(): int;

    /**
     * @param int $value
     * @return DeliveryDateDataInterface
     */
    public function setDeliveryOption(int $value): DeliveryDateDataInterface;

    /**
     * Optional: preselected shipping method for which we do calculations
     *
     * @return string
     */
    public function getShippingMethod(): string;

    /**
     * Optional: preselected shipping method for which we do calculations
     *
     * @param string|null $value
     * @return DeliveryDateDataInterface
     */
    public function setShippingMethod(?string $value): DeliveryDateDataInterface;
}
