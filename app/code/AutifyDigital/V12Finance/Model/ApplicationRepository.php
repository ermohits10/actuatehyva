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

use AutifyDigital\V12Finance\Api\ApplicationRepositoryInterface;
use AutifyDigital\V12Finance\Api\Data\ApplicationInterfaceFactory;
use AutifyDigital\V12Finance\Api\Data\ApplicationSearchResultsInterfaceFactory;
use AutifyDigital\V12Finance\Model\ResourceModel\Application as ResourceApplication;
use AutifyDigital\V12Finance\Model\ResourceModel\Application\CollectionFactory as ApplicationCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class ApplicationRepository implements ApplicationRepositoryInterface
{

    protected $applicationFactory;

    protected $resource;

    protected $dataApplicationFactory;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    private $storeManager;

    protected $dataObjectProcessor;

    protected $searchResultsFactory;

    protected $applicationCollectionFactory;

    private $collectionProcessor;

    protected $extensionAttributesJoinProcessor;


    /**
     * @param ResourceApplication $resource
     * @param ApplicationFactory $applicationFactory
     * @param ApplicationInterfaceFactory $dataApplicationFactory
     * @param ApplicationCollectionFactory $applicationCollectionFactory
     * @param ApplicationSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceApplication $resource,
        ApplicationFactory $applicationFactory,
        ApplicationInterfaceFactory $dataApplicationFactory,
        ApplicationCollectionFactory $applicationCollectionFactory,
        ApplicationSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->applicationFactory = $applicationFactory;
        $this->applicationCollectionFactory = $applicationCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataApplicationFactory = $dataApplicationFactory;
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
        \AutifyDigital\V12Finance\Api\Data\ApplicationInterface $application
    ) {
        /* if (empty($application->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $application->setStoreId($storeId);
        } */

        $applicationData = $this->extensibleDataObjectConverter->toNestedArray(
            $application,
            [],
            \AutifyDigital\V12Finance\Api\Data\ApplicationInterface::class
        );

        $applicationModel = $this->applicationFactory->create()->setData($applicationData);

        try {
            $this->resource->save($applicationModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the application: %1',
                $exception->getMessage()
            ));
        }
        return $applicationModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($applicationId)
    {
        $application = $this->applicationFactory->create();
        $this->resource->load($application, $applicationId);
        if (!$application->getId()) {
            throw new NoSuchEntityException(__('Application with id "%1" does not exist.', $applicationId));
        }
        return $application->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->applicationCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \AutifyDigital\V12Finance\Api\Data\ApplicationInterface::class
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
        \AutifyDigital\V12Finance\Api\Data\ApplicationInterface $application
    ) {
        try {
            $applicationModel = $this->applicationFactory->create();
            $this->resource->load($applicationModel, $application->getApplicationId());
            $this->resource->delete($applicationModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Application: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($applicationId)
    {
        return $this->delete($this->get($applicationId));
    }
}

