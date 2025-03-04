<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model;

use Magento\Framework\DataObject;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;

/**
 * Storage for delivery data of corresponding address.
 *
 * @method setData($key, $value): DeliveryDateDataInterface
 */
class DeliveryDateData extends DataObject implements DeliveryDateDataInterface
{
    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \MageWorx\DeliveryDate\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryDay(): string
    {
        return (string)$this->getData(static::DELIVERY_DAY_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryDay(string $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_DAY_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryHoursFrom(): int
    {
        return (int)$this->getData(static::DELIVERY_HOURS_FROM_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryHoursFrom(?int $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_HOURS_FROM_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryMinutesFrom(): int
    {
        return (int)$this->getData(static::DELIVERY_MINUTES_FROM_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryMinutesFrom(?int $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_MINUTES_FROM_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryHoursTo(): int
    {
        return (int)$this->getData(static::DELIVERY_HOURS_TO_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryHoursTo(?int $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_HOURS_TO_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryMinutesTo(): int
    {
        return (int)$this->getData(static::DELIVERY_MINUTES_TO_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryMinutesTo(?int $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_MINUTES_TO_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryComment(): string
    {
        return (string)$this->getData(static::DELIVERY_COMMENT_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryComment(?string $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_COMMENT_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryTime(): string
    {
        $time = (string)$this->getData(static::DELIVERY_TIME_KEY);
        if (empty($time)) {
            $deliveryTimeSum = array_sum(
                [
                    $this->getDeliveryHoursFrom(),
                    $this->getDeliveryMinutesFrom(),
                    $this->getDeliveryHoursTo(),
                    $this->getDeliveryMinutesTo()
                ]
            );
            if ($deliveryTimeSum > 0) {
                $time = $this->helper->getTimeStingFromDeliveryDataObject($this);
            }
        }

        return $time;
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryTime(?string $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_TIME_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryOption(): int
    {
        return (int)$this->getData(static::DELIVERY_OPTION_ID_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryOption(int $value): DeliveryDateDataInterface
    {
        return $this->setData(static::DELIVERY_OPTION_ID_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethod(): string
    {
        return (string)$this->getData(static::SELECTED_SHIPPING_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethod(?string $value): DeliveryDateDataInterface
    {
        return $this->setData(static::SELECTED_SHIPPING_METHOD, $value);
    }
}
