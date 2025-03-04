<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use AutifyDigital\V12Finance\Helper\Data;

/**
 * Class Mail
 * @package AutifyDigital\V12Finance\Helper
 */
class Mail extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder@param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $autifyDigitalHelper
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        parent::__construct($context);
    }

    /**
     * @param $template
     * @param array $to
     * @param array $templateParams
     * @param $replyTo
     * @param null $storeId
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function sendEmailTemplate(
        $template,
        $to = [],
        $templateParams = [],
        $replyTo = null,
        $storeId = null
    ) {
        if (!isset($to['email']) || empty($to['email'])) {
            throw new LocalizedException(
                __('We could not send the email because the receiver data is invalid.')
            );
        }
        $storeId = $storeId ? $storeId : $this->storeManager->getStore()->getId();
        $name = isset($to['name']) ? $to['name'] : '';

        $senderemail = $this->scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE, $storeId);
        $sendername = $this->scopeConfig->getValue('trans_email/ident_sales/name', ScopeInterface::SCOPE_STORE, $storeId);

        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder
            ->setTemplateIdentifier(
                $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId)
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )
            ->setFromByScope(array('email' => $senderemail, 'name' => $sendername))
            ->setTemplateVars($templateParams)
            ->addTo(
                $to['email'],
                $name
            )
            ->setReplyTo($replyTo)
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     * @param $finance
     * @param $to
     * @param $order
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendPendingEmail(
        $finance,
        $to,
        $order
    ) {
        $applicationStatus = Data::V12_STATUS_ARRAY[$finance->getApplicationStatus()];

        $message = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/pending_email_template_accepted');
        if($finance->getApplicationStatus() === '1'):
            $message = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/pending_email_template_ack');
        endif;

        $templateVarsArray = array(
            'customer_name' => $order->getCustomerName(),
            'order_id' => $order->getIncrementId(),
            'application_id' => $finance->getFinanceApplicationId(),
            'status' => $applicationStatus.' state',
            'status_message' => $message
        );

        $this->sendEmailTemplate(
            'autifydigital/v12finance/email_template',
            array('email' => $to),
            $templateVarsArray,
            $to
        );
    }

    /**
     * @param $orderId
     * @param $customerName
     * @param $to
     * @param string $applicationId
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendDeclinedEmail(
        $orderId,
        $customerName,
        $to,
        $applicationId = ''
    ) {

        if($applicationId){
            $applicationId = ' #'. $applicationId;
        }

        $templateVarsArray = array(
            'customer_name' => $customerName,
            'order_id' => $orderId,
            'application_id' => $applicationId
        );

        $this->sendEmailTemplate(
            'autifydigital/v12finance/declinedemail_template',
            array('email' => $to),
            $templateVarsArray,
            $to
        );
    }

    /**
     * @param $orderId
     * @param $customerName
     * @param $to
     * @param string $applicationId
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendErrorEmail(
        $orderId,
        $customerName,
        $to,
        $applicationId = ''
    ) {

        if($applicationId){
            $applicationId = ' #'. $applicationId;
        }

        $templateVarsArray = array(
            'customer_name' => $customerName,
            'order_id' => $orderId,
            'application_id' => $applicationId
        );

        $this->sendEmailTemplate(
            'autifydigital/v12finance/erroremail_template',
            array('email' => $to),
            $templateVarsArray,
            $to
        );
    }

    /**
     * @param $orderId
     * @param $customerName
     * @param $to
     * @param string $applicationId
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendCanceledEmail(
        $orderId,
        $customerName,
        $to,
        $applicationId = ''
    ) {

        if($applicationId){
            $applicationId = ' #'. $applicationId;
        }

        $templateVarsArray = array(
            'customer_name' => $customerName,
            'order_id' => $orderId,
            'application_id' => $applicationId
        );

        $this->sendEmailTemplate(
            'autifydigital/v12finance/cancelledemail_template',
            array('email' => $to),
            $templateVarsArray,
            $to
        );
    }

    /**
     * @param $orderId
     * @param $customerName
     * @param $to
     * @param string $applicationId
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendAwaitingContractEmail(
        $orderId,
        $customerName,
        $to,
        $applicationId = ''
    ) {

        if($applicationId){
            $applicationId = ' #'. $applicationId;
        }

        $templateVarsArray = array(
            'customer_name' => $customerName,
            'order_id' => $orderId,
            'application_id' => $applicationId
        );

        $this->sendEmailTemplate(
            'autifydigital/v12finance/awaitingcontract_template',
            array('email' => $to),
            $templateVarsArray,
            $to
        );
    }
}

