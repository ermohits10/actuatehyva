<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;

class DeliveryOption extends AbstractDb
{
    /**
     * Serializable field
     *
     * @var array
     */
    protected $_serializableFields = [
        DeliveryOptionInterface::KEY_LIMITS_SERIALIZED   => [["default" => ["time_limits" => []]], []],
        DeliveryOptionInterface::KEY_HOLIDAYS_SERIALIZED => [[], []],
        DeliveryOptionInterface::KEY_DELIVERY_DATE_REQUIRED_ERROR_MESSAGE => [[], []],
        DeliveryOptionInterface::KEY_CONDITIONS_SERIALIZED => [[], []],
    ];

    /**
     * Comma separated fields
     *
     * @var array
     */
    protected $commaSeparatedFields = [
        DeliveryOptionInterface::KEY_METHODS,
        DeliveryOptionInterface::KEY_WORKING_DAYS
    ];

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            DeliveryOptionInterface::TABLE_NAME,
            DeliveryOptionInterface::KEY_ID
        );
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $object */
        if ($object->getId()) {
            $this->loadLinkedData($object);
        }

        $this->unpackCommaSeparatedFields($object);

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel|DeliveryOptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loadLinkedData(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $object */
        $connection = $this->getConnection();
        $select     = $connection->select();
        $select->from(
            $this->getTable(DeliveryOptionInterface::STORE_GROUP_TABLE_NAME),
            [
                'delivery_option_id' => 'delivery_option_id',
                'store_ids'          => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `store_id`)'),
                'customer_group_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `customer_group_id`)')
            ]
        )
               ->group('delivery_option_id')
               ->where('delivery_option_id = ?', $object->getId());

        $result = $connection->fetchAssoc($select);

        if (!empty($result[$object->getId()])) {
            $data             = $result[$object->getId()];
            $storeIds         = explode(',', (string)$data['store_ids']);
            $customerGroupIds = explode(',', (string)$data['customer_group_ids']);
            $object->setStoreIds($storeIds)
                   ->setCustomerGroups($customerGroupIds);
        } else {
            $object->setStoreIds([])
                   ->setCustomerGroups([]);
        }

        return $object;
    }

    /**
     * Unpack comma-separated fields in the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    private function unpackCommaSeparatedFields(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->commaSeparatedFields as $field) {
            if (is_array($object->getData($field))) {
                $object->setData($field, explode(',', $object->getData($field)));
            }
        }
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\MageWorx\DeliveryDate\Model\DeliveryOption $object
     * @return AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->packCommaSeparatedFields($object);

        return parent::_beforeSave($object);
    }

    /**
     * Pack comma-separated fields in the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    private function packCommaSeparatedFields(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->commaSeparatedFields as $field) {
            if (is_array($object->getData($field))) {
                $object->setData($field, implode(',', $object->getData($field)));
            }
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return AbstractDb
     * @throws LocalizedException
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        $this->updateCustomerGroupIdsStoreIds($object);

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateCustomerGroupIdsStoreIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $object */
        $id          = $object->getId();
        $connection  = $this->getConnection();
        $rowsDeleted = $connection->delete(
            $this->getTable(DeliveryOptionInterface::STORE_GROUP_TABLE_NAME),
            'delivery_option_id = ' . $id
        );

        $pairs = [];
        foreach ($object->getStoreIds() as $storeId) {
            if ($object->getCustomerGroups() && !empty($object->getCustomerGroups())) {
                foreach ($object->getCustomerGroups() as $customerGroupId) {
                    $pairs[] = [
                        'delivery_option_id' => $id,
                        'store_id'           => $storeId,
                        'customer_group_id'  => $customerGroupId
                    ];
                }
            } else {
                $pairs[] = [
                    'delivery_option_id' => $id,
                    'store_id'           => $storeId,
                    'customer_group_id'  => null
                ];
            }
        }

        if (empty($pairs)) {
            $pairs[] = [
                'delivery_option_id' => $id,
                'store_id'           => 0,
                'customer_group_id'  => null
            ];
        }

        $rowsInserted = $connection->insertArray(
            $this->getTable(DeliveryOptionInterface::STORE_GROUP_TABLE_NAME),
            [
                'delivery_option_id',
                'store_id',
                'customer_group_id'
            ],
            $pairs
        );

        return $rowsInserted - $rowsDeleted;
    }
}
