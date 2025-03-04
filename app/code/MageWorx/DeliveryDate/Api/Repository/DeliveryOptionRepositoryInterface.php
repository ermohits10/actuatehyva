<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface as Entity;
use MageWorx\DeliveryDate\Model\DeliveryOption as EntityModel;

/**
 * Interface DeliveryOptionRepositoryInterface
 *
 */
interface DeliveryOptionRepositoryInterface
{
    const ENTITY_DATA_INTERFACE = Entity::class;

    /**
     * Save entity.
     *
     * @param Entity $entity
     * @return Entity|EntityModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Entity $entity);

    /**
     * Retrieve entity.
     *
     * @param int $entityId
     * @return Entity|EntityModel
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @param Entity|EntityModel $entity
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
     * @return Entity|EntityModel
     */
    public function getEmptyEntity();
}
