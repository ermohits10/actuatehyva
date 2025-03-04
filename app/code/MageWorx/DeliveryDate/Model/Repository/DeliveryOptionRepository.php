<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface as Entity;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterfaceFactory as EntityInterfaceFactory;
use MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface as RepositoryInterface;
use MageWorx\DeliveryDate\Model\DeliveryOption as EntityModel;
use MageWorx\DeliveryDate\Model\DeliveryOptionFactory as EntityFactory;
use MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption as EntityResource;
use MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\CollectionFactory as EntityCollectionFactory;

class DeliveryOptionRepository implements RepositoryInterface
{
    /**
     * @var EntityResource
     */
    protected $resource;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var EntityCollectionFactory
     */
    protected $entityCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var EntityInterfaceFactory
     */
    protected $dataEntityFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param EntityResource $resource
     * @param EntityFactory $entityFactory
     * @param EntityInterfaceFactory $dataEntityFactory
     * @param EntityCollectionFactory $entityCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EntityResource $resource,
        EntityFactory $entityFactory,
        EntityInterfaceFactory $dataEntityFactory,
        EntityCollectionFactory $entityCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource                = $resource;
        $this->entityFactory           = $entityFactory;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->searchResultsFactory    = $searchResultsFactory;
        $this->dataObjectHelper        = $dataObjectHelper;
        $this->dataEntityFactory       = $dataEntityFactory;
        $this->dataObjectProcessor     = $dataObjectProcessor;
        $this->storeManager            = $storeManager;
    }

    /**
     * Save Entity data
     *
     * @param Entity $entity
     * @return Entity
     * @throws CouldNotSaveException
     */
    public function save(Entity $entity)
    {
        try {
            if (!$entity instanceof AbstractModel) {
                throw new LocalizedException(__('Entity must be instance of \Magento\Framework\Model\AbstractModel'));
            }
            /** @var Entity|EntityModel $entity */
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the entity: %1',
                    $exception->getMessage()
                )
            );
        }

        return $entity;
    }

    /**
     * Load Entity data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @param bool $returnRawObjects
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria, $returnRawObjects = false)
    {
        /** @var \Magento\Framework\Api\SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\Collection $collection */
        $collection = $this->entityCollectionFactory->create();

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [
                        $condition => $filter->getValue()
                    ]
                );
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $entities = [];
        /** @var Entity|EntityModel $entityModel */
        foreach ($collection as $entityModel) {
            if ($returnRawObjects) {
                $entities[] = $entityModel;
            } else {
                /** @var Entity $entityData */
                $entityData = $this->dataEntityFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $entityData,
                    $entityModel->getData(),
                    static::ENTITY_DATA_INTERFACE
                );
                $entities[] = $this->dataObjectProcessor->buildOutputDataArray(
                    $entityData,
                    static::ENTITY_DATA_INTERFACE
                );
            }
        }
        $searchResults->setItems($entities);

        return $searchResults;
    }

    /**
     * Delete Entity by given Entity Identity
     *
     * @param string $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * Delete Entity
     *
     * @param Entity|EntityModel $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Entity $entity)
    {
        try {
            $this->resource->delete($entity);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the entity: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * Load Entity data by given Entity Identity
     *
     * @param string $entityId
     * @return Entity
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId)
    {
        /** @var Entity|EntityModel $entity */
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $entityId);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Entity with id "%1" does not exist.', $entityId));
        }

        return $entity;
    }

    /**
     * Get empty Entity
     *
     * @return Entity|EntityModel
     */
    public function getEmptyEntity()
    {
        /** @var Entity|EntityModel $entity */
        $entity = $this->entityFactory->create();

        return $entity;
    }
}
