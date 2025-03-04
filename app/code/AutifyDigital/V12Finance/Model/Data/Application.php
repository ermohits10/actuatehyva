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

use AutifyDigital\V12Finance\Api\Data\ApplicationInterface;

class Application extends \Magento\Framework\Api\AbstractExtensibleObject implements ApplicationInterface
{

    /**
     * Get application_id
     * @return string|null
     */
    public function getApplicationId()
    {
        return $this->_get(self::APPLICATION_ID);
    }

    /**
     * Set application_id
     * @param string $applicationId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationId($applicationId)
    {
        return $this->setData(self::APPLICATION_ID, $applicationId);
    }

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \AutifyDigital\V12Finance\Api\Data\ApplicationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \AutifyDigital\V12Finance\Api\Data\ApplicationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get order_increment_id
     * @return string|null
     */
    public function getOrderIncrementId()
    {
        return $this->_get(self::ORDER_INCREMENT_ID);
    }

    /**
     * Set order_increment_id
     * @param string $orderIncrementId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * Get customer_email
     * @return string|null
     */
    public function getCustomerEmail()
    {
        return $this->_get(self::CUSTOMER_EMAIL);
    }

    /**
     * Set customer_email
     * @param string $customerEmail
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * Get retailer_product_guid
     * @return string|null
     */
    public function getRetailerProductGuid()
    {
        return $this->_get(self::RETAILER_PRODUCT_GUID);
    }

    /**
     * Set retailer_product_guid
     * @param string $retailerProductGuid
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setRetailerProductGuid($retailerProductGuid)
    {
        return $this->setData(self::RETAILER_PRODUCT_GUID, $retailerProductGuid);
    }

    /**
     * Get retailer_id
     * @return string|null
     */
    public function getRetailerId()
    {
        return $this->_get(self::RETAILER_ID);
    }

    /**
     * Set retailer_id
     * @param string $retailerId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setRetailerId($retailerId)
    {
        return $this->setData(self::RETAILER_ID, $retailerId);
    }

    /**
     * Get finance_length
     * @return string|null
     */
    public function getFinanceLength()
    {
        return $this->_get(self::FINANCE_LENGTH);
    }

    /**
     * Set finance_length
     * @param string $financeLength
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setFinanceLength($financeLength)
    {
        return $this->setData(self::FINANCE_LENGTH, $financeLength);
    }

    /**
     * Get order_amount
     * @return string|null
     */
    public function getOrderAmount()
    {
        return $this->_get(self::ORDER_AMOUNT);
    }

    /**
     * Set order_amount
     * @param string $orderAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setOrderAmount($orderAmount)
    {
        return $this->setData(self::ORDER_AMOUNT, $orderAmount);
    }

    /**
     * Get deposit_amount
     * @return string|null
     */
    public function getDepositAmount()
    {
        return $this->_get(self::DEPOSIT_AMOUNT);
    }

    /**
     * Set deposit_amount
     * @param string $depositAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setDepositAmount($depositAmount)
    {
        return $this->setData(self::DEPOSIT_AMOUNT, $depositAmount);
    }

    /**
     * Get interest_amount
     * @return string|null
     */
    public function getInterestAmount()
    {
        return $this->_get(self::INTEREST_AMOUNT);
    }

    /**
     * Set interest_amount
     * @param string $interestAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setInterestAmount($interestAmount)
    {
        return $this->setData(self::INTEREST_AMOUNT, $interestAmount);
    }

    /**
     * Get finance_amount
     * @return string|null
     */
    public function getFinanceAmount()
    {
        return $this->_get(self::FINANCE_AMOUNT);
    }

    /**
     * Set finance_amount
     * @param string $financeAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setFinanceAmount($financeAmount)
    {
        return $this->setData(self::FINANCE_AMOUNT, $financeAmount);
    }

    /**
     * Get total_amount_payable
     * @return string|null
     */
    public function getTotalAmountPayable()
    {
        return $this->_get(self::TOTAL_AMOUNT_PAYABLE);
    }

    /**
     * Set total_amount_payable
     * @param string $totalAmountPayable
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setTotalAmountPayable($totalAmountPayable)
    {
        return $this->setData(self::TOTAL_AMOUNT_PAYABLE, $totalAmountPayable);
    }

    /**
     * Get application_status
     * @return string|null
     */
    public function getApplicationStatus()
    {
        return $this->_get(self::APPLICATION_STATUS);
    }

    /**
     * Set application_status
     * @param string $applicationStatus
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationStatus($applicationStatus)
    {
        return $this->setData(self::APPLICATION_STATUS, $applicationStatus);
    }

    /**
     * Get pending_email_sent
     * @return string|null
     */
    public function getPendingEmailSent()
    {
        return $this->_get(self::PENDING_EMAIL_SENT);
    }

    /**
     * Set pending_email_sent
     * @param string $pendingEmailSent
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setPendingEmailSent($pendingEmailSent)
    {
        return $this->setData(self::PENDING_EMAIL_SENT, $pendingEmailSent);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get card_summary
     * @return string|null
     */
    public function getCardSummary()
    {
        return $this->_get(self::CARD_SUMMARY);
    }

    /**
     * Set card_summary
     * @param string $cardSummary
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setCardSummary($cardSummary)
    {
        return $this->setData(self::CARD_SUMMARY, $cardSummary);
    }

    /**
     * Get analytics_sent
     * @return string|null
     */
    public function getAnalyticsSent()
    {
        return $this->_get(self::ANALYTICS_SENT);
    }

    /**
     * Set analytics_sent
     * @param string $analyticsSent
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setAnalyticsSent($analyticsSent)
    {
        return $this->setData(self::ANALYTICS_SENT, $analyticsSent);
    }

    /**
     * Get ads_sent
     * @return string|null
     */
    public function getAdsSent()
    {
        return $this->_get(self::ADS_SENT);
    }

    /**
     * Set ads_sent
     * @param string $adsSent
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setAdsSent($adsSent)
    {
        return $this->setData(self::ADS_SENT, $adsSent);
    }

    /**
     * Get finance_application_id
     * @return string|null
     */
    public function getFinanceApplicationId()
    {
        return $this->_get(self::FINANCE_APPLICATION_ID);
    }

    /**
     * Set finance_application_id
     * @param string $financeApplicationId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setFinanceApplicationId($financeApplicationId)
    {
        return $this->setData(self::FINANCE_APPLICATION_ID, $financeApplicationId);
    }

    /**
     * Get application_form_url
     * @return string|null
     */
    public function getApplicationFormUrl()
    {
        return $this->_get(self::APPLICATION_FORM_URL);
    }

    /**
     * Set application_form_url
     * @param string $applicationFormUrl
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationFormUrl($applicationFormUrl)
    {
        return $this->setData(self::APPLICATION_FORM_URL, $applicationFormUrl);
    }

    /**
     * Get application_guid
     * @return string|null
     */
    public function getApplicationGuid()
    {
        return $this->_get(self::APPLICATION_GUID);
    }

    /**
     * Set application_guid
     * @param string $applicationGuid
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationGuid($applicationGuid)
    {
        return $this->setData(self::APPLICATION_GUID, $applicationGuid);
    }

    /**
     * Get authorization_code
     * @return string|null
     */
    public function getAuthorizationCode()
    {
        return $this->_get(self::AUTHORIZATION_CODE);
    }

    /**
     * Set authorization_code
     * @param string $authorizationCode
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setAuthorizationCode($authorizationCode)
    {
        return $this->setData(self::AUTHORIZATION_CODE, $authorizationCode);
    }

}

