<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Repository;

use DateTimeInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
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
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface as Entity;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterfaceFactory as EntityInterfaceFactory;
use MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface as RepositoryInterface;
use MageWorx\DeliveryDate\Model\QueueFactory as EntityFactory;
use MageWorx\DeliveryDate\Model\ResourceModel\Queue as EntityResource;
use MageWorx\DeliveryDate\Model\ResourceModel\Queue\CollectionFactory as EntityCollectionFactory;

class QueueRepository implements RepositoryInterface
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
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

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
        EntityResource                $resource,
        EntityFactory                 $entityFactory,
        EntityInterfaceFactory        $dataEntityFactory,
        EntityCollectionFactory       $entityCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper              $dataObjectHelper,
        DataObjectProcessor           $dataObjectProcessor,
        CollectionProcessorInterface  $collectionProcessor,
        StoreManagerInterface         $storeManager
    ) {
        $this->resource                = $resource;
        $this->entityFactory           = $entityFactory;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->searchResultsFactory    = $searchResultsFactory;
        $this->dataObjectHelper        = $dataObjectHelper;
        $this->dataEntityFactory       = $dataEntityFactory;
        $this->dataObjectProcessor     = $dataObjectProcessor;
        $this->collectionProcessor     = $collectionProcessor;
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
            /** @var Entity $entity */
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

        /** @var \MageWorx\DeliveryDate\Model\ResourceModel\Queue\Collection $collection */
        $collection = $this->entityCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $entities = [];
        /** @var Entity|AbstractModel $entityModel */
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
        $searchResults->setTotalCount(count($entities));

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
     * @param Entity $entity
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
        /** @var Entity $entity */
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
     * @return Entity
     */
    public function getEmptyEntity()
    {
        /** @var Entity $entity */
        $entity = $this->entityFactory->create();

        return $entity;
    }

    /**
     * Get count of dates reserved for criteria by date
     *
     * @param SearchCriteriaInterface $criteria
     * @return array
     */
    public function getQueueReservedCountByDays(SearchCriteriaInterface $criteria): array
    {
        // Preparing collection
        /** @var \MageWorx\DeliveryDate\Model\ResourceModel\Queue\Collection $collection */
        $collection = $this->entityCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $itemsByDate = $collection->getItemsCountByField('delivery_day');

        return $itemsByDate;
    }

    /**
     * Get reserved quotes by date and time diapasons
     *
     * @param SearchCriteriaInterface $criteria
     * @return array
     */
    public function getQueueReservedCountByDateAndTime(SearchCriteriaInterface $criteria): array
    {
        // Preparing collection
        /** @var \MageWorx\DeliveryDate\Model\ResourceModel\Queue\Collection $collection */
        $collection = $this->entityCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);
        $select = $collection->getSelect();
        $select->reset('columns')
               ->columns(
                   [
                       'delivery_day'      => 'delivery_day',
                       'delivery_time'     => new \Zend_Db_Expr(
                           'CONCAT(
                       delivery_hours_from * 60 + delivery_minutes_from, \'_\',
                       delivery_hours_to * 60 + delivery_minutes_to
           )'
                       ),
                       'order_address_ids' => new \Zend_Db_Expr(
                           'GROUP_CONCAT(DISTINCT order_address_id SEPARATOR \',\')'
                       ),
                       'total_count'       => new \Zend_Db_Expr('SUM(1)')
                   ]
               )
               ->group(['delivery_day', 'delivery_time']);

        $itemsByDateAndTime = [];
        $items              = $collection->getConnection()->fetchAll($select);
        foreach ($items as $item) {
            $itemsByDateAndTime[$item['delivery_day']][$item['delivery_time']] = [
                'reserved'          => (int)$item['total_count'],
                'order_address_ids' => (string)$item['order_address_ids']
            ];
        }

        return $itemsByDateAndTime;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return array
     */
    public function getOrderAddressIdsByDay(SearchCriteriaInterface $criteria): array
    {
        // Preparing collection
        /** @var \MageWorx\DeliveryDate\Model\ResourceModel\Queue\Collection $collection */
        $collection = $this->entityCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);
        $select = $collection->getSelect();
        $select->reset('columns')
               ->columns(
                   [
                       'delivery_day'      => 'delivery_day',
                       'order_address_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT order_address_id SEPARATOR \',\')')
                   ]
               )
               ->group(['delivery_day']);

        $itemsByDate = [];
        $items       = $collection->getConnection()->fetchAll($select);
        foreach ($items as $item) {
            $itemsByDate[$item['delivery_day']] = (string)$item['order_address_ids'];
        }

        return $itemsByDate;
    }
}
