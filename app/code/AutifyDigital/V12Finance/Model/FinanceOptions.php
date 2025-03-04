<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */


namespace AutifyDigital\V12Finance\Model;

use AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface;
use AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class FinanceOptions extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'autifydigital_v12finance_financeoptions';
    protected $dataObjectHelper;

    protected $financeoptionsDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param FinanceOptionsInterfaceFactory $financeoptionsDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions $resource
     * @param \AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        FinanceOptionsInterfaceFactory $financeoptionsDataFactory,
        DataObjectHelper $dataObjectHelper,
        \AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions $resource,
        \AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions\Collection $resourceCollection,
        array $data = []
    ) {
        $this->financeoptionsDataFactory = $financeoptionsDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve financeoptions model with financeoptions data
     * @return FinanceOptionsInterface
     */
    public function getDataModel()
    {
        $financeoptionsData = $this->getData();

        $financeoptionsDataObject = $this->financeoptionsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $financeoptionsDataObject,
            $financeoptionsData,
            FinanceOptionsInterface::class
        );

        return $financeoptionsDataObject;
    }
}

