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

interface FinanceOptionsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const FINANCE_NAME = 'finance_name';
    const CALCULATION_FACTOR = 'calculation_factor';
    const CONTRACT_LENGTH = 'contract_length';
    const FINANCEOPTIONS_ID = 'financeoptions_id';
    const FINANCE_ID = 'finance_id';
    const INTEREST_RATE = 'interest_rate';
    const FINANCE_GUID = 'finance_guid';
    const MIN_LOAN = 'min_loan';
    const MAX_LOAN = 'max_loan';

    /**
     * Get financeoptions_id
     * @return string|null
     */
    public function getFinanceoptionsId();

    /**
     * Set financeoptions_id
     * @param string $financeoptionsId
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceoptionsId($financeoptionsId);

    /**
     * Get finance_id
     * @return string|null
     */
    public function getFinanceId();

    /**
     * Set finance_id
     * @param string $financeId
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceId($financeId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \AutifyDigital\V12Finance\Api\Data\FinanceOptionsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \AutifyDigital\V12Finance\Api\Data\FinanceOptionsExtensionInterface $extensionAttributes
    );

    /**
     * Get finance_guid
     * @return string|null
     */
    public function getFinanceGuid();

    /**
     * Set finance_guid
     * @param string $financeGuid
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceGuid($financeGuid);

    /**
     * Get finance_name
     * @return string|null
     */
    public function getFinanceName();

    /**
     * Set finance_name
     * @param string $financeName
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceName($financeName);

    /**
     * Get interest_rate
     * @return string|null
     */
    public function getInterestRate();

    /**
     * Set interest_rate
     * @param string $interestRate
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setInterestRate($interestRate);

    /**
     * Get contract_length
     * @return string|null
     */
    public function getContractLength();

    /**
     * Set contract_length
     * @param string $contractLength
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setContractLength($contractLength);

    /**
     * Get calculation_factor
     * @return string|null
     */
    public function getCalculationFactor();

    /**
     * Set calculation_factor
     * @param string $calculationFactor
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setCalculationFactor($calculationFactor);

    /**
     * Get min_loan
     * @return string|null
     */
    public function getMinLoan();

    /**
     * Set min_loan
     * @param string $minLoan
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setMinLoan($minLoan);

    /**
     * Get max_loan
     * @return string|null
     */
    public function getMaxLoan();

    /**
     * Set max_loan
     * @param string $maxLoan
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setMaxLoan($maxLoan);
}

