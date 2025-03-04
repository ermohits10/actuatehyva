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

use AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface;
use AutifyDigital\V12Finance\Api\Data\PriceOptionsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class PriceOptions extends \Magento\Framework\Model\AbstractModel
{

    protected $priceoptionsDataFactory;

    protected $_eventPrefix = 'autifydigital_v12finance_priceoptions';
    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PriceOptionsInterfaceFactory $priceoptionsDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \AutifyDigital\V12Finance\Model\ResourceModel\PriceOptions $resource
     * @param \AutifyDigital\V12Finance\Model\ResourceModel\PriceOptions\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        PriceOptionsInterfaceFactory $priceoptionsDataFactory,
        DataObjectHelper $dataObjectHelper,
        \AutifyDigital\V12Finance\Model\ResourceModel\PriceOptions $resource,
        \AutifyDigital\V12Finance\Model\ResourceModel\PriceOptions\Collection $resourceCollection,
        array $data = []
    ) {
        $this->priceoptionsDataFactory = $priceoptionsDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve priceoptions model with priceoptions data
     * @return PriceOptionsInterface
     */
    public function getDataModel()
    {
        $priceoptionsData = $this->getData();

        $priceoptionsDataObject = $this->priceoptionsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $priceoptionsDataObject,
            $priceoptionsData,
            PriceOptionsInterface::class
        );

        return $priceoptionsDataObject;
    }
}

