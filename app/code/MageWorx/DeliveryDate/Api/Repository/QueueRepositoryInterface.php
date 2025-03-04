<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api\Repository;

use DateTimeInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface as Entity;

/**
 * Interface QueueRepositoryInterface
 *
 */
interface QueueRepositoryInterface
{
    const ENTITY_DATA_INTERFACE = Entity::class;

    /**
     * Save entity.
     *
     * @param Entity $entity
     * @return Entity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Entity $entity);

    /**
     * Retrieve entity.
     *
     * @param int $entityId
     * @return Entity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($entityId);

    /**
     * Retrieve entities matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $returnRawObjects
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $returnRawObjects = false);

    /**
     * Delete entity.
     *
     * @param Entity $entity
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Entity $entity);

    /**
     * Delete entity by ID.
     *
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

    /**
     * Get empty Entity
     *
     * @return Entity
     */
    public function getEmptyEntity();

    /**
     * Get count of dates reserved for criteria by date
     *
     * @param SearchCriteriaInterface $criteria
     * @return array
     */
    public function getQueueReservedCountByDays(SearchCriteriaInterface $criteria): array;

    /**
     * Get reserved quotes by date and time diapasons
     *
     * @param SearchCriteriaInterface $criteria
     * @return array
     */
    public function getQueueReservedCountByDateAndTime(SearchCriteriaInterface $criteria): array;

    /**
     * @param SearchCriteriaInterface $criteria
     * @return array
     */
    public function getOrderAddressIdsByDay(SearchCriteriaInterface $criteria): array;
}
