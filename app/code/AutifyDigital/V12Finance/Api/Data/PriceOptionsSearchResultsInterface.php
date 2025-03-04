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

interface PriceOptionsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PriceOptions list.
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface[]
     */
    public function getItems();

    /**
     * Set price_from list.
     * @param \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

