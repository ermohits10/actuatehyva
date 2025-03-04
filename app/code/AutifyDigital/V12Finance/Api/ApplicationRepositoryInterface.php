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

interface ApplicationRepositoryInterface
{

    /**
     * Save Application
     * @param \AutifyDigital\V12Finance\Api\Data\ApplicationInterface $application
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \AutifyDigital\V12Finance\Api\Data\ApplicationInterface $application
    );

    /**
     * Retrieve Application
     * @param string $applicationId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($applicationId);

    /**
     * Retrieve Application matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Application
     * @param \AutifyDigital\V12Finance\Api\Data\ApplicationInterface $application
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \AutifyDigital\V12Finance\Api\Data\ApplicationInterface $application
    );

    /**
     * Delete Application by ID
     * @param string $applicationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($applicationId);
}

