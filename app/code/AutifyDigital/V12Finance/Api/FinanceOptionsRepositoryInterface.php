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

interface FinanceOptionsRepositoryInterface
{

    /**
     * Save FinanceOptions
     * @param \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface $financeOptions
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface $financeOptions
    );

    /**
     * Retrieve FinanceOptions
     * @param string $financeoptionsId
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($financeoptionsId);

    /**
     * Retrieve FinanceOptions matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete FinanceOptions
     * @param \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface $financeOptions
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface $financeOptions
    );

    /**
     * Delete FinanceOptions by ID
     * @param string $financeoptionsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($financeoptionsId);
}

