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

interface FinanceOptionsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get FinanceOptions list.
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface[]
     */
    public function getItems();

    /**
     * Set finance_id list.
     * @param \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

