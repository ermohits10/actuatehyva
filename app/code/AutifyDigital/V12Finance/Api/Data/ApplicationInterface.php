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

interface ApplicationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const FINANCE_LENGTH = 'finance_length';
    const UPDATED_AT = 'updated_at';
    const FINANCE_AMOUNT = 'finance_amount';
    const PENDING_EMAIL_SENT = 'pending_email_sent';
    const INTEREST_AMOUNT = 'interest_amount';
    const ORDER_ID = 'order_id';
    const TOTAL_AMOUNT_PAYABLE = 'total_amount_payable';
    const APPLICATION_STATUS = 'application_status';
    const RETAILER_PRODUCT_GUID = 'retailer_product_guid';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const RETAILER_ID = 'retailer_id';
    const ORDER_AMOUNT = 'order_amount';
    const DEPOSIT_AMOUNT = 'deposit_amount';
    const CUSTOMER_EMAIL = 'customer_email';
    const APPLICATION_ID = 'application_id';
    const CREATED_AT = 'created_at';
    const FINANCE_APPLICATION_ID = 'finance_application_id';
    const APPLICATION_FORM_URL = 'application_form_url';
    const APPLICATION_GUID = 'application_guid';
    const AUTHORIZATION_CODE = 'authorization_code';
    const CARD_SUMMARY = 'card_summary';
    const ANALYTICS_SENT = 'analytics_sent';
    const ADS_SENT = 'ads_sent';

    /**
     * Get application_id
     * @return string|null
     */
    public function getApplicationId();

    /**
     * Set application_id
     * @param string $applicationId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationId($applicationId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setOrderId($orderId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \AutifyDigital\V12Finance\Api\Data\ApplicationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \AutifyDigital\V12Finance\Api\Data\ApplicationExtensionInterface $extensionAttributes
    );

    /**
     * Get order_increment_id
     * @return string|null
     */
    public function getOrderIncrementId();

    /**
     * Set order_increment_id
     * @param string $orderIncrementId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * Get customer_email
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Set customer_email
     * @param string $customerEmail
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get retailer_product_guid
     * @return string|null
     */
    public function getRetailerProductGuid();

    /**
     * Set retailer_product_guid
     * @param string $retailerProductGuid
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setRetailerProductGuid($retailerProductGuid);

    /**
     * Get retailer_id
     * @return string|null
     */
    public function getRetailerId();

    /**
     * Set retailer_id
     * @param string $retailerId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setRetailerId($retailerId);

    /**
     * Get finance_length
     * @return string|null
     */
    public function getFinanceLength();

    /**
     * Set finance_length
     * @param string $financeLength
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setFinanceLength($financeLength);

    /**
     * Get order_amount
     * @return string|null
     */
    public function getOrderAmount();

    /**
     * Set order_amount
     * @param string $orderAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setOrderAmount($orderAmount);

    /**
     * Get deposit_amount
     * @return string|null
     */
    public function getDepositAmount();

    /**
     * Set deposit_amount
     * @param string $depositAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setDepositAmount($depositAmount);

    /**
     * Get interest_amount
     * @return string|null
     */
    public function getInterestAmount();

    /**
     * Set interest_amount
     * @param string $interestAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setInterestAmount($interestAmount);

    /**
     * Get finance_amount
     * @return string|null
     */
    public function getFinanceAmount();

    /**
     * Set finance_amount
     * @param string $financeAmount
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setFinanceAmount($financeAmount);

    /**
     * Get total_amount_payable
     * @return string|null
     */
    public function getTotalAmountPayable();

    /**
     * Set total_amount_payable
     * @param string $totalAmountPayable
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setTotalAmountPayable($totalAmountPayable);

    /**
     * Get application_status
     * @return string|null
     */
    public function getApplicationStatus();

    /**
     * Set application_status
     * @param string $applicationStatus
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationStatus($applicationStatus);

    /**
     * Get pending_email_sent
     * @return string|null
     */
    public function getPendingEmailSent();

    /**
     * Set pending_email_sent
     * @param string $pendingEmailSent
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setPendingEmailSent($pendingEmailSent);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get card_summary
     * @return string|null
     */
    public function getCardSummary();

    /**
     * Set card_summary
     * @param string $cardSummary
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setCardSummary($cardSummary);

    /**
     * Get analytics_sent
     * @return string|null
     */
    public function getAnalyticsSent();

    /**
     * Set analytics_sent
     * @param string $analyticsSent
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setAnalyticsSent($analyticsSent);

    /**
     * Get ads_sent
     * @return string|null
     */
    public function getAdsSent();

    /**
     * Set ads_sent
     * @param string $adsSent
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setAdsSent($adsSent);

    /**
     * Get finance_application_id
     * @return string|null
     */
    public function getFinanceApplicationId();

    /**
     * Set finance_application_id
     * @param string $financeApplicationId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setFinanceApplicationId($financeApplicationId);

    /**
     * Get application_form_url
     * @return string|null
     */
    public function getApplicationFormUrl();

    /**
     * Set application_form_url
     * @param string $applicationFormUrl
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationFormUrl($applicationFormUrl);

    /**
     * Get application_guid
     * @return string|null
     */
    public function getApplicationGuid();

    /**
     * Set application_guid
     * @param string $applicationId
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setApplicationGuid($applicationGuid);

    /**
     * Get authorization_code
     * @return string|null
     */
    public function getAuthorizationCode();

    /**
     * Set authorization_code
     * @param string $authorizationCode
     * @return \AutifyDigital\V12Finance\Api\Data\ApplicationInterface
     */
    public function setAuthorizationCode($authorizationCode);

}

