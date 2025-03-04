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

use AutifyDigital\V12Finance\Api\Data\ApplicationInterface;
use AutifyDigital\V12Finance\Api\Data\ApplicationInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Application extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'autifydigital_v12finance_application';
    protected $applicationDataFactory;

    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ApplicationInterfaceFactory $applicationDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \AutifyDigital\V12Finance\Model\ResourceModel\Application $resource
     * @param \AutifyDigital\V12Finance\Model\ResourceModel\Application\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ApplicationInterfaceFactory $applicationDataFactory,
        DataObjectHelper $dataObjectHelper,
        \AutifyDigital\V12Finance\Model\ResourceModel\Application $resource,
        \AutifyDigital\V12Finance\Model\ResourceModel\Application\Collection $resourceCollection,
        array $data = []
    ) {
        $this->applicationDataFactory = $applicationDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve application model with application data
     * @return ApplicationInterface
     */
    public function getDataModel()
    {
        $applicationData = $this->getData();

        $applicationDataObject = $this->applicationDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $applicationDataObject,
            $applicationData,
            ApplicationInterface::class
        );

        return $applicationDataObject;
    }
}

