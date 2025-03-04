<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use MageWorx\DeliveryDate\Api\DeliveryOptionConditionsInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;

class Collection extends AbstractCollection
{
    const ALL_STORE_VIEWS_VALUE = "0";

    protected $arrayFields = [
        'store_ids',
        'customer_group_ids'
    ];

    protected $storeGroupAddedFlag = false;

    /**
     * @param \DateTime|string $date
     * @return \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\Collection
     */
    public function addDateFilter($date)
    {
        if (!$date) {
            $date = 'now';
        } elseif (is_string($date)) {
            $date = strtotime($date);
        }

        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        $dateForFilter = $date->format('Y-m-d');

        $this->getSelect()->where(
            DeliveryOptionInterface::KEY_ACTIVE_FROM
            . ' is null or '
            . DeliveryOptionInterface::KEY_ACTIVE_FROM
            . ' <= ?',
            $dateForFilter
        )->where(
            DeliveryOptionInterface::KEY_ACTIVE_TO
            . ' is null or '
            . DeliveryOptionInterface::KEY_ACTIVE_TO
            . ' >= ?',
            $dateForFilter
        );

        return $this;
    }

    /**
     * Customer group filter
     *
     * @param int $customerGroupId
     * @return Collection
     */
    public function addCustomerGroupFilter(int $customerGroupId): Collection
    {
        $this->joinStoreGroupTable();
        parent::addFieldToFilter(
            'store_group.customer_group_id',
            [
                ['eq' => $customerGroupId],
                ['null' => true]
            ]
        );

        return $this;
    }

    /**
     * Join store-group table
     *
     */
    protected function joinStoreGroupTable()
    {
        if (!$this->getFlag('is_store_group_table_joined')) {
            $this->setFlag('is_store_group_table_joined', true);
            $this->getSelect()->joinLeft(
                ['store_group' => $this->getTable(DeliveryOptionInterface::STORE_GROUP_TABLE_NAME)],
                'main_table.' . DeliveryOptionInterface::KEY_ID . ' = store_group.delivery_option_id',
                []
            );
            $this->getSelect()->distinct(true);
        }
    }

    /**
     * Provide support for store id filter
     *
     * @param string $field
     * @param null|string|array $condition
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'store') {
            return $this->addStoreFilter($condition);
        }

        if ($field == 'store_id') {
            return $this->addStoreFilter($condition);
        }

        if ($field == 'methods') {
            if (isset($condition['eq'])) {
                $method = $condition['eq'];
            } else {
                $method = $condition;
            }

            return $this->addShippingMethodFilter($method);
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }

    /**
     * Limit delivery collection by specific stores
     *
     * @param int|int[]|Store $condition
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addStoreFilter($condition)
    {
        $finalCondition = [
            0 => ['eq' => '0']
        ];

        $this->joinStoreGroupTable();
        if ($condition instanceof Store) {
            $storeId = $condition->getId();
            array_push($finalCondition, ['eq' => $storeId]);
        }

        if (is_array($condition)) {
            array_push($finalCondition, $condition);
        } elseif (is_int($condition) || is_string($condition)) {
            array_push($finalCondition, ['eq' => $condition]);
        }

        parent::addFieldToFilter(
            'store_group.store_id',
            $finalCondition
        );

        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function addShippingMethodFilter($method)
    {
        $conditions = [
            $this->_translateCondition(
                DeliveryOptionInterface::KEY_METHODS,
                [
                    ['like' => $method],
                    ['like' => '%,' . $method],
                    ['like' => $method . ',%'],
                    ['like' => '%,' . $method . ',%'],
                    ['null' => true]
                ]
            ),
            $this->_translateCondition(
                DeliveryOptionInterface::KEY_SHIPPING_METHODS_CHOICE_LIMITER,
                [
                    ['eq' => DeliveryOptionInterface::SHIPPING_METHODS_CHOICE_LIMIT_ALL_METHODS]
                ]
            )
        ];

        $resultCondition = '(' . implode(') ' . Select::SQL_OR . ' (', $conditions) . ')';
        $this->_select->where($resultCondition, null, Select::TYPE_CONDITION);

        return $this;
    }

    /**
     * Filter available delivery options by conditions object
     *
     * @param DeliveryOptionConditionsInterface $deliveryOptionConditions
     * @return Collection
     * @throws LocalizedException
     */
    public function addFilterByConditions(DeliveryOptionConditionsInterface $deliveryOptionConditions): Collection
    {
        if ($deliveryOptionConditions->getShippingMethod()) {
            $this->addShippingMethodFilter($deliveryOptionConditions->getShippingMethod());
        }

        if ($deliveryOptionConditions->getStoreId()) {
            $this->addStoreFilter($deliveryOptionConditions->getStoreId());
        }

        if ($deliveryOptionConditions->getCustomerGroupId() !== null) {
            $this->addCustomerGroupFilter($deliveryOptionConditions->getCustomerGroupId());
        }

        return $this;
    }

    /**
     * Check conditions which could not be used as sql filter. Return only one delivery option using sort order
     * (Delivery option having less sort order will be returned as better much).
     *
     * @param DeliveryOptionConditionsInterface $deliveryOptionConditions
     * @return DeliveryOptionInterface|null
     */
    public function getSuitableDeliveryOptionByConditions(
        DeliveryOptionConditionsInterface $deliveryOptionConditions
    ): ?DeliveryOptionInterface {
        /** @var DeliveryOptionInterface[] $loadedDeliveryOptions */
        $loadedDeliveryOptions = $this->getItems();

        $address = $deliveryOptionConditions->getAddress();
        if ($address->getPostcode()) {
            $loadedDeliveryOptions = $this->filterByZipCode($address->getPostcode(), $loadedDeliveryOptions);
        }

        // Used to fix array of delivery options by sort order: DO having less sort order must be first
        usort($loadedDeliveryOptions, function ($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return !empty($loadedDeliveryOptions) ? reset($loadedDeliveryOptions) : null;
    }

    /**
     * @param string $zip
     * @param DeliveryOptionInterface[] $items
     * @return DeliveryOptionInterface[]
     */
    protected function filterByZipCode(string $zip, array $items): array
    {
        foreach ($items as $key => $item) {
            $conditions = $item->getConditionsSerialized();
            if (empty($conditions) || empty($conditions['zips'])) {
                continue; // pass validation because has no conditions at all
            }

            $zipConditions = $conditions['zips'];
            if (empty($zipConditions['plain']) && empty($zipConditions['diapason'])) {
                continue; // pass validation because both conditions are empty (workaround for array with empty value)
            }

            $plainZips = $zipConditions['plain'] ?? [];

            if (is_string($plainZips)) {
                $plainZips = explode(',', $plainZips);
                $plainZips = array_map('trim', $plainZips);
            }

            if (!empty($plainZips) && in_array($zip, $plainZips)) {
                continue; // pass validation by strict zip code
            }

            $zipDiapasons = $zipConditions['diapason'] ?? [];
            if (!empty($zipDiapasons)) {
                foreach ($zipDiapasons as $diapason) {
                    if ($zip >= $diapason['from'] && $zip <= $diapason['to']) {
                        continue 2; // pass validation by zip code diapason
                    }
                }
            }

            // Do not pass validation, remove item from set
            unset($items[$key]);
        }

        return $items;
    }

    /**
     * Convert collection to array
     *
     * @param array $arrRequiredFields
     * @return array
     */
    public function toArray($arrRequiredFields = [])
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $this->getSize();

        $arrItems['items'] = [];
        foreach ($this as $item) {
            $arrItems['items'][] = $item->toArray($arrRequiredFields);
        }

        return $arrItems;
    }

    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \MageWorx\DeliveryDate\Model\DeliveryOption::class,
            \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption::class
        );
        $this->_setIdFieldName(\MageWorx\DeliveryDate\Api\DeliveryOptionInterface::KEY_ID);
    }

    /**
     * Let do something before add loaded item in collection
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        $item = parent::beforeAddLoadedItem($item);
        /** @var \MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption $resource */
        $resource = $item->getResource();
        $resource->unserializeFields($item);

        foreach ($this->arrayFields as $fieldKey) {
            if (!isset($item[$fieldKey]) || $item[$fieldKey] === '' || $item[$fieldKey] === null) {
                $value = $fieldKey != 'store_ids' ? [] : [static::ALL_STORE_VIEWS_VALUE];
            } elseif (is_array($item[$fieldKey])) {
                continue;
            } else {
                $value = explode(',', $item[$fieldKey]);
            }

            $item[$fieldKey] = $value;
        }

        return $item;
    }
}
