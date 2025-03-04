<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */


namespace AutifyDigital\V12Finance\Model\Data;

use AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface;

class PriceOptions extends \Magento\Framework\Api\AbstractExtensibleObject implements PriceOptionsInterface
{

    /**
     * Get priceoptions_id
     * @return string|null
     */
    public function getPriceoptionsId()
    {
        return $this->_get(self::PRICEOPTIONS_ID);
    }

    /**
     * Set priceoptions_id
     * @param string $priceoptionsId
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setPriceoptionsId($priceoptionsId)
    {
        return $this->setData(self::PRICEOPTIONS_ID, $priceoptionsId);
    }

    /**
     * Get price_from
     * @return string|null
     */
    public function getPriceFrom()
    {
        return $this->_get(self::PRICE_FROM);
    }

    /**
     * Set price_from
     * @param string $priceFrom
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setPriceFrom($priceFrom)
    {
        return $this->setData(self::PRICE_FROM, $priceFrom);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \AutifyDigital\V12Finance\Api\Data\PriceOptionsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \AutifyDigital\V12Finance\Api\Data\PriceOptionsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get price_to
     * @return string|null
     */
    public function getPriceTo()
    {
        return $this->_get(self::PRICE_TO);
    }

    /**
     * Set price_to
     * @param string $priceTo
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setPriceTo($priceTo)
    {
        return $this->setData(self::PRICE_TO, $priceTo);
    }

    /**
     * Get finance_options
     * @return string|null
     */
    public function getFinanceOptions()
    {
        return $this->_get(self::FINANCE_OPTIONS);
    }

    /**
     * Set finance_options
     * @param string $financeOptions
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setFinanceOptions($financeOptions)
    {
        return $this->setData(self::FINANCE_OPTIONS, $financeOptions);
    }
}

