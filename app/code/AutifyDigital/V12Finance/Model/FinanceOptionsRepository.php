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

use AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterfaceFactory;
use AutifyDigital\V12Finance\Api\Data\FinanceOptionsSearchResultsInterfaceFactory;
use AutifyDigital\V12Finance\Api\FinanceOptionsRepositoryInterface;
use AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions as ResourceFinanceOptions;
use AutifyDigital\V12Finance\Model\ResourceModel\FinanceOptions\CollectionFactory as FinanceOptionsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class FinanceOptionsRepository implements FinanceOptionsRepositoryInterface
{

    protected $resource;

    protected $dataObjectHelper;

    protected $dataFinanceOptionsFactory;

    protected $extensibleDataObjectConverter;
    protected $financeOptionsFactory;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $financeOptionsCollectionFactory;


    /**
     * @param ResourceFinanceOptions $resource
     * @param FinanceOptionsFactory $financeOptionsFactory
     * @param FinanceOptionsInterfaceFactory $dataFinanceOptionsFactory
     * @param FinanceOptionsCollectionFactory $financeOptionsCollectionFactory
     * @param FinanceOptionsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceFinanceOptions $resource,
        FinanceOptionsFactory $financeOptionsFactory,
        FinanceOptionsInterfaceFactory $dataFinanceOptionsFactory,
        FinanceOptionsCollectionFactory $financeOptionsCollectionFactory,
        FinanceOptionsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->financeOptionsFactory = $financeOptionsFactory;
        $this->financeOptionsCollectionFactory = $financeOptionsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFinanceOptionsFactory = $dataFinanceOptionsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface $financeOptions
    ) {
        /* if (empty($financeOptions->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $financeOptions->setStoreId($storeId);
        } */

        $financeOptionsData = $this->extensibleDataObjectConverter->toNestedArray(
            $financeOptions,
            [],
            \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface::class
        );

        $financeOptionsModel = $this->financeOptionsFactory->create()->setData($financeOptionsData);

        try {
            $this->resource->save($financeOptionsModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the financeOptions: %1',
                $exception->getMessage()
            ));
        }
        return $financeOptionsModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($financeOptionsId)
    {
        $financeOptions = $this->financeOptionsFactory->create();
        $this->resource->load($financeOptions, $financeOptionsId);
        if (!$financeOptions->getId()) {
            throw new NoSuchEntityException(__('FinanceOptions with id "%1" does not exist.', $financeOptionsId));
        }
        return $financeOptions->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->financeOptionsCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \AutifyDigital\V12Finance\Api\Data\FinanceOptionsInterface $financeOptions
    ) {
        try {
            $financeOptionsModel = $this->financeOptionsFactory->create();
            $this->resource->load($financeOptionsModel, $financeOptions->getFinanceoptionsId());
            $this->resource->delete($financeOptionsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the FinanceOptions: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($financeOptionsId)
    {
        return $this->delete($this->get($financeOptionsId));
    }
}

