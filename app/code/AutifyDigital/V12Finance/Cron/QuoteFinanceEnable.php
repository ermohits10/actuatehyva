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
 * Class QuoteFinanceEnable
 * @package AutifyDigital\V12Finance\Cron
 */
class QuoteFinanceEnable
{

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * Constructor
     *
     * @param Data $autifyDigitalHelper
     */
    public function __construct(
        Data $autifyDigitalHelper
    ) {
        $this->autifyDigitalHelper = $autifyDigitalHelper;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $checkQuoteCronEnable = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/quote_cron_enable');
        if($checkQuoteCronEnable === '1') {
            $this->autifyDigitalHelper->addLog("Quote Finance Removal Start");
            $quoteCronHours = $this->autifyDigitalHelper->getConfig('autifydigital/v12finance/quote_cron_enable_hours');

            $getApplicationHours = ($quoteCronHours) ? -abs($quoteCronHours) : -6;
            $to = $this->autifyDigitalHelper->getDateTime();
            $from = strtotime($getApplicationHours . " hours", strtotime($to));
            $from = date('Y-m-d h:i:s', $from);
            //Get All 0 Finance Quote
            $getAllQuote = $this->autifyDigitalHelper->getQuoteFactory()
                    ->getCollection()
                    ->addFieldToFilter("v12_finance_enable", array('eq' => 0))
                    ->addFieldToFilter("finance_updated_at", array('lteq' => $from));

            foreach ($getAllQuote as $quote) {
                $quote->setData('v12_finance_enable', '1')
                    ->setData('finance_updated_at', '')
                    ->save();
                $this->autifyDigitalHelper->addLog(__("%1 has been removed from the V12 restriction.", $quote->getId()));
            }
            //Logic to get finance
            $this->autifyDigitalHelper->addLog("Quote Finance Removal End");
        } else {
            $this->autifyDigitalHelper->addLog("Quote remove finance is disabled.");
        }

    }
}

