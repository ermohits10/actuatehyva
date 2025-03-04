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

use AutifyDigital\V12Finance\Api\Data\PriceOptionsInterfaceFactory;
use AutifyDigital\V12Finance\Api\Data\PriceOptionsSearchResultsInterfaceFactory;
use AutifyDigital\V12Finance\Api\PriceOptionsRepositoryInterface;
use AutifyDigital\V12Finance\Model\ResourceModel\PriceOptions as ResourcePriceOptions;
use AutifyDigital\V12Finance\Model\ResourceModel\PriceOptions\CollectionFactory as PriceOptionsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class PriceOptionsRepository implements PriceOptionsRepositoryInterface
{

    protected $resource;

    protected $dataPriceOptionsFactory;

    protected $dataObjectHelper;

    protected $priceOptionsFactory;

    protected $extensibleDataObjectConverter;
    private $storeManager;

    protected $dataObjectProcessor;

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $priceOptionsCollectionFactory;


    /**
     * @param ResourcePriceOptions $resource
     * @param PriceOptionsFactory $priceOptionsFactory
     * @param PriceOptionsInterfaceFactory $dataPriceOptionsFactory
     * @param PriceOptionsCollectionFactory $priceOptionsCollectionFactory
     * @param PriceOptionsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourcePriceOptions $resource,
        PriceOptionsFactory $priceOptionsFactory,
        PriceOptionsInterfaceFactory $dataPriceOptionsFactory,
        PriceOptionsCollectionFactory $priceOptionsCollectionFactory,
        PriceOptionsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->priceOptionsFactory = $priceOptionsFactory;
        $this->priceOptionsCollectionFactory = $priceOptionsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPriceOptionsFactory = $dataPriceOptionsFactory;
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
        \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface $priceOptions
    ) {
        /* if (empty($priceOptions->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $priceOptions->setStoreId($storeId);
        } */

        $priceOptionsData = $this->extensibleDataObjectConverter->toNestedArray(
            $priceOptions,
            [],
            \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface::class
        );

        $priceOptionsModel = $this->priceOptionsFactory->create()->setData($priceOptionsData);

        try {
            $this->resource->save($priceOptionsModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the priceOptions: %1',
                $exception->getMessage()
            ));
        }
        return $priceOptionsModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($priceOptionsId)
    {
        $priceOptions = $this->priceOptionsFactory->create();
        $this->resource->load($priceOptions, $priceOptionsId);
        if (!$priceOptions->getId()) {
            throw new NoSuchEntityException(__('PriceOptions with id "%1" does not exist.', $priceOptionsId));
        }
        return $priceOptions->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->priceOptionsCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface::class
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
        \AutifyDigital\V12Finance\Api\Data\PriceOptionsInterface $priceOptions
    ) {
        try {
            $priceOptionsModel = $this->priceOptionsFactory->create();
            $this->resource->load($priceOptionsModel, $priceOptions->getPriceoptionsId());
            $this->resource->delete($priceOptionsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the PriceOptions: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($priceOptionsId)
    {
        return $this->delete($this->get($priceOptionsId));
    }
}

