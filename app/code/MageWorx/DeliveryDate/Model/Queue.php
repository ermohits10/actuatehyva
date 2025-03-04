<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model;

use Magento\Framework\Model\AbstractModel;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class Queue extends AbstractModel implements QueueDataInterface
{
    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\DeliveryDate\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return int
     */
    public function getQuoteAddressId()
    {
        return $this->getData(static::QUOTE_ADDRESS_ID_KEY);
    }

    /**
     * @param int $value
     * @return QueueDataInterface
     */
    public function setQuoteAddressId($value)
    {
        return $this->setData(static::QUOTE_ADDRESS_ID_KEY, $value);
    }

    /**
     * @return int
     */
    public function getOrderAddressId()
    {
        return $this->getData(static::ORDER_ADDRESS_ID_KEY);
    }

    /**
     * @param int $value
     * @return QueueDataInterface
     */
    public function setOrderAddressId($value)
    {
        return $this->setData(static::ORDER_ADDRESS_ID_KEY, $value);
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return (string)$this->getData(static::SHIPPING_METHOD_KEY);
    }

    /**
     * @param string $value
     * @return QueueDataInterface
     */
    public function setShippingMethod(?string $value): QueueDataInterface
    {
        return $this->setData(static::SHIPPING_METHOD_KEY, $value);
    }

    /**
     * @return string
     */
    public function getCarrier()
    {
        return $this->getData(static::CARRIER_KEY);
    }

    /**
     * @param string $value
     * @return QueueDataInterface
     */
    public function setCarrier($value)
    {
        return $this->setData(static::CARRIER_KEY, $value);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(static::STORE_ID_KEY);
    }

    /**
     * @param int $value
     * @return QueueDataInterface
     */
    public function setStoreId($value)
    {
        return $this->setData(static::STORE_ID_KEY, $value);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\MageWorx\DeliveryDate\Model\ResourceModel\Queue::class);
        $this->setIdFieldName('entity_id');
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
    public function setDeliveryDay(string $value): QueueDataInterface
    {
        return $this->setData(static::DELIVERY_DAY_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryHoursFrom(): ?int
    {
        return (int)$this->getData(static::DELIVERY_HOURS_FROM_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryHoursFrom(?int $value): QueueDataInterface
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
    public function setDeliveryMinutesFrom(?int $value): QueueDataInterface
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
    public function setDeliveryHoursTo(?int $value): QueueDataInterface
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
    public function setDeliveryMinutesTo(?int $value): QueueDataInterface
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
    public function setDeliveryComment(?string $value): QueueDataInterface
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
    public function setDeliveryTime(?string $value): QueueDataInterface
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
    public function setDeliveryOption(int $value): QueueDataInterface
    {
        return $this->setData(static::DELIVERY_OPTION_ID_KEY, $value);
    }
}
