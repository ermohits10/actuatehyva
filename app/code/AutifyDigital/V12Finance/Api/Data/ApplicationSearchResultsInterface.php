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

interface ApplicationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Application list.
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface[]
     */
    public function getItems();

    /**
     * Set order_id list.
     * @param \AutifyDigital\V12Finance\Api\Data\ApplicationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

