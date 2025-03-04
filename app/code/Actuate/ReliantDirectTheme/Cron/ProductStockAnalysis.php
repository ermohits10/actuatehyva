<?php

namespace Actuate\ReliantDirectTheme\Cron;

use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

class ProductStockAnalysis
{
    private const STOCK_ANALYSIS_ATTRIBUTE_CODE = 'stockanalysis';
    private const WEIGHT_ATTRIBUTE_CODE = 'weight';
    private LoggerInterface $logger;
    private ResourceConnection $resourceConnection;
    private $connection = null;
    private StockRegistryInterface $stockRegistry;
    private Action $productAction;
    private array $availableStoreId = [];

    /**
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param StockRegistryInterface $stockRegistry
     * @param Action $productAction
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        StockRegistryInterface $stockRegistry,
        Action $productAction
    ) {
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->stockRegistry = $stockRegistry;
        $this->productAction = $productAction;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $this->getAllProductStockAnalysis();
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }

    /**
     * @throws \Exception
     */
    private function getAllProductStockAnalysis()
    {
        $connection = $this->getConnection();
        $sqlQuery = $connection->select()->from(
            ['ea' => $this->resourceConnection->getTableName('eav_attribute')], ['ea.attribute_id']
        )->joinInner(
            ['cpet' => $this->resourceConnection->getTableName('catalog_product_entity_text')],
            'ea.attribute_id = cpet.attribute_id',
            ['cpet.entity_id', 'cpet.store_id', 'cpet.value']
        )->where('ea.attribute_code = ?', self::STOCK_ANALYSIS_ATTRIBUTE_CODE);
        $result = $connection->fetchAll($sqlQuery);

        foreach ($result as $item) {
            $xml = simplexml_load_string($item['value'], "SimpleXMLElement", LIBXML_NOCDATA);;
            $xmlToArray = json_decode(json_encode($xml), true);
            if (isset($xmlToArray['AdditionalAvailability']) && !empty($xmlToArray['AdditionalAvailability'])) {
                $stockData = $this->getSupplierStockData($xmlToArray['AdditionalAvailability']);
                if (!empty($stockData) && isset($stockData['Quantity']) && !empty($stockData['Quantity'])) {
                    $this->updateStockQtyAndStatus($stockData, $item['entity_id']);
                }
            }
        }
        $this->checkAndCreateUrlRewriteEntries();
    }

    /**
     * @param $stockData
     * @return array|mixed
     */
    private function getSupplierStockData($stockData)
    {
        return $stockData['ProductStockAvailabilitySchedule'] ?? [];
    }

    /**
     * @param $stockData
     * @param $productId
     */
    private function updateStockQtyAndStatus($stockData, $productId)
    {
        $this->logger->info('Updating stock data from stock analysis attribute for product id: ' . $productId);
        try {
            $stockItem = $this->stockRegistry->getStockItem($productId);
            $stockItem->setData('is_in_stock', 1);
            $stockItem->setData('qty', $stockData['Quantity']);
            $stockItem->setData('manage_stock', 1);
            $stockItem->setData('use_config_notify_stock_qty', 1);
            $stockItem->save();
            $this->logger->info('Stock Data has been updated for product id: ' . $productId);
            $this->updateWeightIfZero($productId);
            $this->updateParentProduct($productId);
        } catch (\Exception $e) {
            $this->logger->error('Error while updating stock data for product id ' . $productId . ': ' . $e->getMessage());
        }
    }

    /**
     * @param $productId
     */
    private function updateWeightIfZero($productId)
    {
        $connection = $this->getConnection();
        $sqlQuery = $connection->select()->from(
            ['ea' => $this->resourceConnection->getTableName('eav_attribute')], ['ea.attribute_id']
        )->joinInner(
            ['cped' => $this->resourceConnection->getTableName('catalog_product_entity_decimal')],
            'ea.attribute_id = cped.attribute_id',
            ['cped.entity_id', 'cped.store_id', 'cped.value']
        )
            ->where('ea.attribute_code = ?', self::WEIGHT_ATTRIBUTE_CODE)
            ->where('cped.value <= ?', 0)
            ->where('cped.entity_id = ?', $productId);

        $result = $connection->fetchAll($sqlQuery);
        if (count($result) > 0) {
            foreach ($result as $item) {
                try {
                    $this->productAction->updateAttributes([$productId], [self::WEIGHT_ATTRIBUTE_CODE => '1'], $item['store_id']);
                } catch (\Exception $e) {
                    $this->logger->error('Error while updating weight for product id ' . $productId . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * @param $productId
     */
    private function updateParentProduct($productId) {
        $connection = $this->getConnection();
        $sqlQuery = $connection->select()->from(
            ['cpsl' => $this->resourceConnection->getTableName('catalog_product_super_link')], ['cpsl.parent_id']
        )->where('cpsl.product_id = ?', $productId);
        $parentProduct = $connection->fetchRow($sqlQuery);
        if (!empty($parentProduct)) {
            try {
                $stockItem = $this->stockRegistry->getStockItem($parentProduct['parent_id']);
                $stockItem->setData('is_in_stock', 1);
                $stockItem->setData('manage_stock', 1);
                $stockItem->setData('use_config_notify_stock_qty', 1);
                $stockItem->save();
                $this->logger->info('Stock Data has been updated for parent product id: ' . $parentProduct['parent_id']);
            } catch (\Exception $e) {
                $this->logger->error('Error while updating stock data for parent product id ' . $parentProduct['parent_id'] . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    private function getStoreId()
    {
        if (empty($this->availableStoreId)) {
            $connection = $this->getConnection();
            $sqlQuery = $connection->select()->from(['cpe' => $this->resourceConnection->getTableName('store')], 'store_id')
                ->where('store_id != ?', 0);
            $this->availableStoreId = $connection->fetchAll($sqlQuery);
        }
        return $this->availableStoreId;
    }

    private function checkAndCreateUrlRewriteEntries()
    {
        $storeIds = $this->getStoreId();
        $connection = $this->getConnection();
        $sqlQuery = $connection->select()->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'cpe.entity_id'
        )
            ->joinLeft(
                ['ur' => $this->resourceConnection->getTableName('url_rewrite')],
                'cpe.entity_id = ur.entity_id AND ur.entity_type = "product" AND ur.metadata IS NULL',
                ['ur.url_rewrite_id']
            )
            ->joinInner(
                ['cpei' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
                'cpe.entity_id = cpei.entity_id AND cpei.attribute_id = 99',
                ['cpei.value_id']
            )
            ->joinInner(
                ['cpev' => $this->resourceConnection->getTableName('catalog_product_entity_varchar')],
                'cpe.entity_id = cpev.entity_id AND cpev.attribute_id = 121',
                ['cpev.value', 'cpev.store_id']
            )
            ->where('cpe.type_id = ?', 'simple')
            ->where('cpei.value = ?', 4)
            ->where('ur.url_rewrite_id IS NULL');

        $result = $connection->fetchAll($sqlQuery);
        if (count($result) > 0) {
            foreach ($result as $item) {
                foreach ($storeIds as $storeId) {
                    $data = [
                        'entity_type' => 'product',
                        'entity_id' => $item['entity_id'],
                        'request_path' => $item['value'],
                        'target_path' => 'catalog/product/view/id/' . $item['entity_id'],
                        'store_id' => $storeId['store_id'],
                        'is_autogenerated' => '1',
                    ];
                    $connection->insertOnDuplicate(
                        $this->resourceConnection->getTableName('url_rewrite'),
                        $data,
                        ['request_path', 'store_id']
                    );
                }
            }
        }
    }
}
