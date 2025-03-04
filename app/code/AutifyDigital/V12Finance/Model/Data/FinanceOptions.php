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

use AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface;

class FinanceOptions extends \Magento\Framework\Api\AbstractExtensibleObject implements FinanceOptionsInterface
{

    /**
     * Get financeoptions_id
     * @return string|null
     */
    public function getFinanceoptionsId()
    {
        return $this->_get(self::FINANCEOPTIONS_ID);
    }

    /**
     * Set financeoptions_id
     * @param string $financeoptionsId
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceoptionsId($financeoptionsId)
    {
        return $this->setData(self::FINANCEOPTIONS_ID, $financeoptionsId);
    }

    /**
     * Get finance_id
     * @return string|null
     */
    public function getFinanceId()
    {
        return $this->_get(self::FINANCE_ID);
    }

    /**
     * Set finance_id
     * @param string $financeId
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceId($financeId)
    {
        return $this->setData(self::FINANCE_ID, $financeId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \AutifyDigital\V12Finance\Api\Data\FinanceOptionsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \AutifyDigital\V12Finance\Api\Data\FinanceOptionsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get finance_guid
     * @return string|null
     */
    public function getFinanceGuid()
    {
        return $this->_get(self::FINANCE_GUID);
    }

    /**
     * Set finance_guid
     * @param string $financeGuid
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceGuid($financeGuid)
    {
        return $this->setData(self::FINANCE_GUID, $financeGuid);
    }

    /**
     * Get finance_name
     * @return string|null
     */
    public function getFinanceName()
    {
        return $this->_get(self::FINANCE_NAME);
    }

    /**
     * Set finance_name
     * @param string $financeName
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setFinanceName($financeName)
    {
        return $this->setData(self::FINANCE_NAME, $financeName);
    }

    /**
     * Get interest_rate
     * @return string|null
     */
    public function getInterestRate()
    {
        return $this->_get(self::INTEREST_RATE);
    }

    /**
     * Set interest_rate
     * @param string $interestRate
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setInterestRate($interestRate)
    {
        return $this->setData(self::INTEREST_RATE, $interestRate);
    }

    /**
     * Get contract_length
     * @return string|null
     */
    public function getContractLength()
    {
        return $this->_get(self::CONTRACT_LENGTH);
    }

    /**
     * Set contract_length
     * @param string $contractLength
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setContractLength($contractLength)
    {
        return $this->setData(self::CONTRACT_LENGTH, $contractLength);
    }

    /**
     * Get calculation_factor
     * @return string|null
     */
    public function getCalculationFactor()
    {
        return $this->_get(self::CALCULATION_FACTOR);
    }

    /**
     * Set calculation_factor
     * @param string $calculationFactor
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setCalculationFactor($calculationFactor)
    {
        return $this->setData(self::CALCULATION_FACTOR, $calculationFactor);
    }

    /**
     * Get min_loan
     * @return string|null
     */
    public function getMinLoan()
    {
        return $this->_get(self::MIN_LOAN);
    }

    /**
     * Set min_loan
     * @param string $minLoan
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setMinLoan($minLoan)
    {
        return $this->setData(self::MIN_LOAN, $minLoan);
    }

    /**
     * Get max_loan
     * @return string|null
     */
    public function getMaxLoan()
    {
        return $this->_get(self::MAX_LOAN);
    }

    /**
     * Set max_loan
     * @param string $maxLoan
     * @return \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface
     */
    public function setMaxLoan($maxLoan)
    {
        return $this->setData(self::MAX_LOAN, $maxLoan);
    }

}

