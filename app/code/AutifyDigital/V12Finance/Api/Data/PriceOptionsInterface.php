<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Api\Data;

interface PriceOptionsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PRICE_TO = 'price_to';
    const PRICEOPTIONS_ID = 'priceoptions_id';
    const PRICE_FROM = 'price_from';
    const FINANCE_OPTIONS = 'finance_options';

    /**
     * Get priceoptions_id
     * @return string|null
     */
    public function getPriceoptionsId();

    /**
     * Set priceoptions_id
     * @param string $priceoptionsId
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setPriceoptionsId($priceoptionsId);

    /**
     * Get price_from
     * @return string|null
     */
    public function getPriceFrom();

    /**
     * Set price_from
     * @param string $priceFrom
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setPriceFrom($priceFrom);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \AutifyDigital\V12Finance\Api\Data\PriceOptionsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \AutifyDigital\V12Finance\Api\Data\PriceOptionsExtensionInterface $extensionAttributes
    );

    /**
     * Get price_to
     * @return string|null
     */
    public function getPriceTo();

    /**
     * Set price_to
     * @param string $priceTo
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setPriceTo($priceTo);

    /**
     * Get finance_options
     * @return string|null
     */
    public function getFinanceOptions();

    /**
     * Set finance_options
     * @param string $financeOptions
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     */
    public function setFinanceOptions($financeOptions);
}

