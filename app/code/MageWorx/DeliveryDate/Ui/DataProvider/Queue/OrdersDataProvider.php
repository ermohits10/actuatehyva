<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\DataProvider\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\DataProvider\AbstractDataProvider;
use MageWorx\DeliveryDate\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as OrderAddressCollectionFactory;

/**
 * Class OrdersDataProvider
 *
 * Stores all orders corresponding the queue list (by order_address_id)
 *
 */
class OrdersDataProvider extends AbstractDataProvider
{
    const LISTING_NAME                 = 'mageworx_delivery_date_queue_orders_listing_data_source';
    const DELIVERY_OPTION_ID_PARAM_KEY = 'delivery_option_id';
    const SALES_ORDER_TABLE_ALIAS      = 'so';

    /**
     * Main collection
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\Collection
     */
    protected $collectionWithFilter;

    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var bool
     */
    protected $entityIdFilterAdded = false;

    /**
     * @var bool
     */
    protected $dateRangeFilterAdded = false;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param OrderAddressCollectionFactory $collectionFactory
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        OrderAddressCollectionFactory $collectionFactory,
        QueueCollectionFactory $queueCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection             = $collectionFactory->create();
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->request                = $request;
        $this->filterBuilder          = $filterBuilder;
        $this->addFieldStrategies     = $addFieldStrategies;
        $this->addFilterStrategies    = $addFilterStrategies;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->addDefaultOrderAddressIdsFilter();
            $this->addDateRangeFilter();
        }
        $this->getCollection()->load();

        $items = $this->getCollection()->toArray();
        if (empty($items['items'])) {
            return [
                'totalRecords' => 0,
                'items'        => []
            ];
        }

        $indexedItems = $items['items'];

        $data = [
            'totalRecords' => count($indexedItems),
            'items'        => $indexedItems,
        ];

        return $data;
    }

    /**
     * Filter collection by order address ids
     */
    private function addDefaultOrderAddressIdsFilter()
    {
        if (!$this->entityIdFilterAdded) {
            $orderAddressIds = $this->getOrderAddressIds();
            $filter          = $this->filterBuilder->setField('main_table.entity_id')
                                                   ->setValue($orderAddressIds)
                                                   ->setConditionType('in')
                                                   ->create();
            $this->addFilter($filter);
            $this->entityIdFilterAdded = true;
        }
    }

    /**
     * Filter collection by date range (default min/max days interval)
     */
    private function addDateRangeFilter()
    {
        if (!$this->dateRangeFilterAdded) {
            $table = $this->collection->getTable('mageworx_dd_queue');

            $minDate         = new \DateTime();
            $minDaysInterval = abs(\MageWorx\DeliveryDate\Helper\Data::MIN_DAYS_RANGE);
            $minDate->sub(new \DateInterval('P' . $minDaysInterval . 'D'));
            $minDateFormatted = $minDate->format('Y-m-d');

            $maxDate         = new \DateTime();
            $maxDaysInterval = abs(\MageWorx\DeliveryDate\Helper\Data::MAX_DAYS_RANGE);
            $maxDate->add(new \DateInterval('P' . $maxDaysInterval . 'D'));
            $maxDateFormatted = $maxDate->format('Y-m-d');

            $filterMinDate = $this->filterBuilder->setField($table . '.delivery_day')
                                                 ->setValue($minDateFormatted)
                                                 ->setConditionType('gteq')
                                                 ->create();
            $this->addFilter($filterMinDate);

            $filterMaxDate = $this->filterBuilder->setField($table . '.delivery_day')
                                                 ->setValue($maxDateFormatted)
                                                 ->setConditionType('lteq')
                                                 ->create();

            $this->addFilter($filterMaxDate);

            $this->dateRangeFilterAdded = true;
        }
    }

    /**
     * @return AbstractCollection|\Magento\Sales\Model\ResourceModel\Order\Address\Collection
     */
    public function getCollection()
    {
        $collection = parent::getCollection();

        return $collection;
    }

    /**
     * @return array
     */
    protected function getOrderAddressIds()
    {
        $deliveryOptionId = $this->getDeliveryOptionId();
        $queueCollection  = $this->queueCollectionFactory->create();
        /** @var array $orderAddressIds */
        $orderAddressIds = $queueCollection->getOrderAddressIdsByDeliveryOptionId($deliveryOptionId);

        return $orderAddressIds;
    }

    /**
     * Get delivery option id from request
     *
     * @return int
     */
    protected function getDeliveryOptionId()
    {
        return (int)$this->request->getParam(static::DELIVERY_OPTION_ID_PARAM_KEY);
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }

        if ($filter->getField() === 'entity_id') {
            $this->entityIdFilterAdded = true;
        }
    }
}
