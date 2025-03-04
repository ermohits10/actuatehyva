<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Plugin;

class AddDeliveryDateDataToOrdersGrid
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * List of data source indexes for which we add delivery date data.
     *
     * @var array
     */
    protected $suitableDataSources = [
        'sales_order_grid_data_source',
        'mageworx_sales_order_grid_data_source'
    ];

    /**
     * AddDeliveryDateDataToOrdersGrid constructor.
     *
     * @param \Magento\Framework\App\State $appState
     * @param \Psr\Log\LoggerInterface $customLogger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $customLogger,
        array $data = []
    ) {
        $this->appState = $appState;
        $this->logger   = $customLogger;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $collection
     * @param $requestName
     * @return mixed
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if (!in_array($requestName, $this->getSuitableDataSources())) {
            return $collection;
        }

        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            if ($this->appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $sqlDump = $collection->getSelectSql(true);
                $this->logger->log(100, 'Before:');
                $this->logger->log(100, $sqlDump);
            }

            try {
                $orderAddressTableName = $collection->getResource()->getTable('sales_order_address');
                $queueTableName        = $collection->getResource()->getTable('mageworx_dd_queue');
                $collection->getSelect()->joinLeft(
                    ['soa' => $orderAddressTableName],
                    'soa.parent_id = main_table.entity_id AND soa.address_type = \'shipping\'',
                    null
                );
                $collection->getSelect()->joinLeft(
                    ['delivery_date_queue' => $queueTableName],
                    'delivery_date_queue.order_address_id = soa.entity_id',
                    [
                        'delivery_day',
                        'delivery_hours_from',
                        'delivery_minutes_from',
                        'delivery_hours_to',
                        'delivery_minutes_to',
                        'delivery_comment',
                    ]
                );
            } catch (\Zend_Db_Select_Exception $selectException) {
                // Do nothing in that case
                $this->logger->log(100, $selectException);
            }
        }

        if ($this->appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $sqlDump = $collection->getSelectSql(true);
            $this->logger->log(100, 'After:');
            $this->logger->log(100, $sqlDump);
        }

        return $collection;
    }

    /**
     * @return array|string[]
     */
    public function getSuitableDataSources(): array
    {
        return $this->suitableDataSources;
    }
}
