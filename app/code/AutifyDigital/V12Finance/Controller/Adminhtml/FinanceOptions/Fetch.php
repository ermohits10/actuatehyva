<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Controller\Adminhtml\FinanceOptions;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Fetch
 *
 * @package AutifyDigital\V12Finance\Controller\Adminhtml
 */
class Fetch extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'AutifyDigital_V12Finance::FinanceOptions_view';

    const GET_FOPTION = 'https://apply.v12finance.com/latest/retailerapi/GetRetailerFinanceProducts';

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifydigitalHelper;

    protected $financeOptionFactory;

    protected $financeCollectionFactory;

    /**
     * Fetch constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \AutifyDigital\V12Finance\Helper\Data $autifydigitalHelper
     * @param \AutifyDigital\V12Finance\Model\FinanceOptionsFactory $financeOptionFactory
     * @param \AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions\CollectionFactory $financeCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \AutifyDigital\V12Finance\Helper\Data $autifydigitalHelper,
        \AutifyDigital\V12Finance\Model\FinanceOptionsFactory $financeOptionFactory,
        \AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions\CollectionFactory $financeCollectionFactory
    ) {
        $this->autifydigitalHelper = $autifydigitalHelper;
        $this->financeOptionFactory = $financeOptionFactory;
        $this->financeCollectionFactory = $financeCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $coreConfigArray = $this->autifydigitalHelper->getCoreConfig();
        $authenticationKey = $coreConfigArray['api_key'];
        $retailerId = $coreConfigArray['retailer_id'];
        $retailerGuid = $coreConfigArray['retailer_guid'];
        if($retailerGuid && $retailerId && $authenticationKey) {
            $callArray = array(
                "Retailer" => array(
                    "AuthenticationKey" => $authenticationKey,
                    "RetailerGuid" => $retailerGuid,
                    "RetailerId" => $retailerId,
                    "UserId" => null
                )
            );

            $this->autifydigitalHelper->addLog('Calling Finance Fetch Request');
            $this->autifydigitalHelper->addLog($callArray, true);
            $curlCall = $this->autifydigitalHelper->callCurl(self::GET_FOPTION, $callArray);

            if ($curlCall['status'] === 'error') {
                $this->autifydigitalHelper->addLog('Curl Call Error');
            } else {
                $jsonDecodeArray = $curlCall['response'];
                $checkErrors = '';
                if(isset($jsonDecodeArray['Errors'][0])) {
                    $checkErrors = $jsonDecodeArray['Errors'][0];
                    $this->autifydigitalHelper->addLog($checkErrors, true);
                } else {
                    $this->autifydigitalHelper->addLog($jsonDecodeArray, true);
                    if(isset($jsonDecodeArray['FinanceProducts'])) {
                        $financeOptions = $jsonDecodeArray['FinanceProducts'];
                        $existFinance = $this->checkFinanceExist();
                        $deleteFinance = array();
                        foreach ($financeOptions as $financeOption) {

                            $financeFactory = $this->financeOptionFactory->create();
                            $productId = $financeOption['ProductId'];
                            $financeKey = array_search($productId, array_column($existFinance, 'finance_id'));
                            $financeOptionExisted = array();

                            if(isset($existFinance[$financeKey])) {
                                $financeOptionExisted = $existFinance[$financeKey];
                                $financeId = $financeOptionExisted['financeoptions_id'];
                                $financeFactory = $financeFactory->load($financeId);
                                $deleteFinance[] = $financeId;
                            }

                            $productGuid = $financeOption['ProductGuid'];
                            $financeName = $financeOption['Name'];
                            $month = $financeOption['Months'];
                            $interestRate = $financeOption['APR'];
                            $calculationFactor =  $financeOption['CalculationFactor'];
                            $minLoan = $financeOption['MinLoan'];
                            $maxLoan = $financeOption['MaxLoan'];

                            $financeData = array(
                                "finance_id"     => $productId,
                                "finance_guid"   => $productGuid,
                                "finance_name"   => $financeName,
                                "interest_rate"  => $interestRate,
                                "contract_length" => $month,
                                "calculation_factor" => $calculationFactor,
                                "min_loan" => $minLoan,
                                "max_loan" => $maxLoan
                            );

                            try {
                                $financeFactory->addData($financeData)->save();
                            } catch (LocalizedException $e) {
                                $this->autifydigitalHelper->addLog($e->getMessage());
                                $this->messageManager->addErrorMessage($e->getMessage());
                            } catch (\Exception $e) {
                                $this->autifydigitalHelper->addLog($e->getMessage());
                                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the records.'));
                            }
                        }
                        $this->deleteFinance($deleteFinance);
                    }
                }
            }
        }
        $this->messageManager->addSuccessMessage( __("We have added/updated finance options."));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    public function checkFinanceExist()
    {
        $financeCollections = $this->financeCollectionFactory->create();
        $financeExist = array();
        $i = 0;
        foreach ($financeCollections as $financeCollection) {
            $financeExist[$i]['finance_id'] = $financeCollection->getFinanceId();
            $financeExist[$i]['financeoptions_id'] = $financeCollection->getFinanceoptionsId();
            $i++;
        }
        return $financeExist;
    }

    public function deleteFinance($delefinance)
    {
        $financeCollections = $this->financeCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('financeoptions_id', array('nin' => $delefinance));

        foreach($financeCollections as $finance) {
            $finance->delete();
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('AutifyDigital_V12Finance::FinanceOptions_save');
    }
}

