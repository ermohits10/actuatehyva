<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Cron;

use \AutifyDigital\V12Finance\Helper\Data;

/**
 * Class ApplicationPendingEmail
 * @package AutifyDigital\V12Finance\Cron
 */
class ApplicationPendingEmail
{

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Mail
     */
    protected $mailHelper;

    /**
     * Constructor
     *
     * @param Data $autifyDigitalHelper
     */
    public function __construct(
        Data $autifyDigitalHelper,
        \AutifyDigital\V12Finance\Helper\Mail $mailHelper
    ) {
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $to = date("Y-m-d h:i:s");
        $from = strtotime("-30 minutes", strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $getAllPendingApplication = $this->autifyDigitalHelper->getAllPendingApplication($from);
        if($this->autifyDigitalHelper->getConfig('autifydigital/v12finance/pending_email_enable') == '1'){
            foreach ($getAllPendingApplication as $finance):
                $order = $this->autifyDigitalHelper->getOrder($finance->getOrderId());
                $applicationId = $finance->getFinanceApplicationId();
                $customerEmail = $finance->getCustomerEmail();
                $currentTime = time();
                $savedTimeTimestamp= strtotime($finance->getCreatedAt());
                $this->autifyDigitalHelper->addLog($savedTimeTimestamp, true);
                if($applicationId && ($currentTime > ($savedTimeTimestamp+900))):
                    $this->mailHelper->sendPendingEmail($finance, $customerEmail, $order);
                    $finance->setPendingEmailSent(1)->save();
                endif;
            endforeach;
        }
    }
}

