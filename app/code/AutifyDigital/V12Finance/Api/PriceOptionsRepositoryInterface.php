<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PriceOptionsRepositoryInterface
{

    /**
     * Save PriceOptions
     * @param \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface $priceOptions
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface $priceOptions
    );

    /**
     * Retrieve PriceOptions
     * @param string $priceoptionsId
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($priceoptionsId);

    /**
     * Retrieve PriceOptions matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \AutifyDigital\V12Finance\Api\Data\PriceOptionsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PriceOptions
     * @param \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface $priceOptions
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface $priceOptions
    );

    /**
     * Delete PriceOptions by ID
     * @param string $priceoptionsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($priceoptionsId);
}

