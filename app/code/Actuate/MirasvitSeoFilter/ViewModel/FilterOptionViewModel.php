<?php

declare(strict_types=1);

namespace Actuate\MirasvitSeoFilter\ViewModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FilterOptionViewModel implements ArgumentInterface
{
    private ResourceConnection $resourceConnection;

    private ?AdapterInterface $connection = null;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return AdapterInterface
     */
    public function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }

    /**
     * @param $attributeCode
     * @return array
     */
    public function getExistsOptions($attributeCode)
    {
        $connection = $this->getConnection();
        $sqlQuery = $connection->select()->from(
            ['fi' => $connection->getTableName('actuate_seo_filter_indexable')],
            ['option', 'is_indexable']
        )->where('attribute_code = ?', $attributeCode)->order('index_id');
        $result = $connection->fetchAll($sqlQuery);
        if (count($result) > 0) {
            return array_column($result, 'is_indexable', 'option');
        }
        return [];
    }

    /**
     * @param $attributes
     * @return array
     */
    public function getExistIndexableDataByAttributes($attributes)
    {
        $connection = $this->getConnection();
        $sqlQuery = $connection->select()->from(
            ['fi' => $connection->getTableName('actuate_seo_filter_indexable')],
            ['option', 'is_indexable']
        )->where('attribute_code in (?)', $attributes)->order('index_id');
        $result = $connection->fetchAll($sqlQuery);
        return $result ?? [];
    }
}
