<?php

namespace Actuate\ReliantDirectTheme\Index\Magento\Catalog\Product;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Config as TaxConfig;
use Mirasvit\Search\Index\Magento\Catalog\Product\InstantProvider\Mapper;
use Mirasvit\Search\Service\IndexService;

class InstantProvider extends \Mirasvit\Search\Index\Magento\Catalog\Product\InstantProvider
{
    private $attributeIds = [];
    private $pkField = '';
    private ResourceConnection $resource;
    private TaxHelper $taxHelper;
    private CatalogHelper $catalogHelper;
    private PricingHelper $pricingHelper;
    private Mapper $mapper;

    /**
     * @param Mapper $mapper
     * @param IndexService $indexService
     * @param ResourceConnection $resource
     * @param TaxHelper $taxHelper
     * @param CatalogHelper $catalogHelper
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        Mapper $mapper,
        IndexService $indexService,
        ResourceConnection $resource,
        TaxHelper $taxHelper,
        CatalogHelper $catalogHelper,
        PricingHelper $pricingHelper
    ) {
        parent::__construct(
            $mapper,
            $indexService
        );
        $this->resource = $resource;
        $this->taxHelper = $taxHelper;
        $this->catalogHelper = $catalogHelper;
        $this->pricingHelper = $pricingHelper;
        $this->mapper = $mapper;
    }

    /**
     * @param int $storeId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getItems(
        int $storeId,
        int $limit,
        int $page = 1
    ): array {
        $priceDisplayType = $this->taxHelper->getPriceDisplayType($storeId);

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $this->getCollection($limit, $page)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('description')
            ->setOrder('relevance');

        $entityIds = [];
        foreach ($collection as $product) {
            $entityIds[] = $product->getId();
        }

        $maps = [
            'sku'          => $this->mapper->mapProductSku($storeId, $entityIds),
            'name'         => $this->mapper->mapProductName($storeId, $entityIds),
            'description'  => $this->mapper->mapProductDescription($storeId, $entityIds),
            'url'          => $this->mapper->mapProductUrl($storeId, $entityIds),
            'imageUrl'     => $this->mapper->mapProductImage($storeId, $entityIds),
            'price'        => $this->mapper->mapProductPrice($storeId, $entityIds),
            'stockStatus'  => $this->mapper->mapProductStockStatus($storeId, $entityIds),
            'addToCartUrl' => $this->mapper->mapProductCart($storeId, $entityIds),
            'rating'       => $this->mapper->mapProductRating($storeId, $entityIds),
            'reviews'      => $this->mapper->mapProductReviews($storeId, $entityIds),
        ];

        $result = [];
        foreach ($maps as $key => $items) {
            foreach ($items as $productId => $value) {
                $result[$productId][$key] = $value;
            }
        }

        $productIds = array_keys($result);

        $priceData = $this->attributeSelectQuery($storeId, $productIds, 'price', 'decimal');
        $mainPrices = [];
        foreach ($productIds as $productId) {
            $mainPrices[$productId] = '';
        }

        foreach ($priceData as $productId => $mainPrice) {
            $mainPrices[$productId] = $mainPrice;
        }

        $data = $this->resource->getConnection()->fetchAll(
            $this->resource->getConnection()
                ->select()
                ->from(['cpip' => $this->resource->getTableName('catalog_product_index_price')], ['*'])
                ->join(['s' => $this->resource->getTableName('store')], 's.website_id = cpip.website_id', [])
                ->where('entity_id IN(?)', $productIds)
                ->where('s.store_id = ?', $storeId)
                ->group('entity_id')
        );

        $taxClassIds = $this->attributeSelectQuery($storeId, $productIds, 'tax_class_id', 'int');

        foreach ($data as $item) {
            $productId = $item['entity_id'];

            $price = 0;
            if ($item['max_price'] != 0) {
                $price = $item['max_price'];
            }
            if ($item['min_price'] != 0) {
                $price = $item['min_price'];
            }
            if ($item['final_price'] != 0) {
                $price = $item['final_price'];
            }

            if ($price <= 0) {
                continue;
            }

            if (isset($mainPrices[$productId]) && $mainPrices[$productId] !== '') {
                $savePrice = (float) $mainPrices[$productId] - (float) $price;
                if ($savePrice > 0) {
                    if ($priceDisplayType === TaxConfig::DISPLAY_TYPE_INCLUDING_TAX) {
                        $product = new \Magento\Framework\DataObject([
                            'tax_class_id' => $taxClassIds[$productId],
                        ]);

                        $savePrice = $this->catalogHelper->getTaxPrice($product, $savePrice, true, null, null, null, $storeId, null, true);
                        $mainPrice = $this->catalogHelper->getTaxPrice($product, $mainPrices[$productId], true, null, null, null, $storeId, null, true);
                        $result[$productId]['savePrice'] = $savePrice;
                        $result[$productId]['mainPrice'] = $mainPrice;
                    }

                    $result[$productId]['savePrice'] = $this->pricingHelper->currencyByStore($savePrice, $storeId, true, false);
                    $result[$productId]['mainPrice'] = $this->pricingHelper->currencyByStore($mainPrices[$productId], $storeId, true, false);
                } else {
                    $result[$productId]['savePrice'] = '';
                    $result[$productId]['mainPrice'] = '';
                }
            } else {
                $allChildProducts = $this->checkAndGetAllChildProductIds($productId);
                if (!empty($allChildProducts)) {
                    $childProductIds = array_column($allChildProducts, 'product_id');
                    $taxClassIds = $this->attributeSelectQuery($storeId, $childProductIds, 'tax_class_id', 'int');
                    $data = $this->resource->getConnection()->fetchAll(
                        $this->resource->getConnection()
                            ->select()
                            ->from(['cpip' => $this->resource->getTableName('catalog_product_index_price')], ['*'])
                            ->join(['s' => $this->resource->getTableName('store')], 's.website_id = cpip.website_id', [])
                            ->where('entity_id IN(?)', $childProductIds)
                            ->where('s.store_id = ?', $storeId)
                            ->group('entity_id')
                    );

                    $matchedChildData = [];
                    if (!empty($data)) {
                        $matchedChildData = array_filter($data, function($dataItem) use ($item) {
                            return (float) $dataItem['final_price'] === (float) $item['min_price'];
                        });
                    }

                    if (!empty($matchedChildData)) {
                        foreach ($matchedChildData as $childData) {
                            $savePrice = (float) $childData['price'] - (float) $childData['final_price'];
                            if ($savePrice > 0) {
                                if ($priceDisplayType === TaxConfig::DISPLAY_TYPE_INCLUDING_TAX) {
                                    $product = new \Magento\Framework\DataObject([
                                        'tax_class_id' => $taxClassIds[$childData['entity_id']],
                                    ]);

                                    $savePrice = $this->catalogHelper->getTaxPrice($product, $savePrice, true, null, null, null, $storeId, null, true);
                                    $mainPrice = $this->catalogHelper->getTaxPrice($product, $childData['price'], true, null, null, null, $storeId, null, true);
                                    $result[$productId]['savePrice'] = $savePrice;
                                    $result[$productId]['mainPrice'] = $mainPrice;
                                }

                                $result[$productId]['savePrice'] = $this->pricingHelper->currencyByStore($savePrice, $storeId, true, false);
                                $result[$productId]['mainPrice'] = $this->pricingHelper->currencyByStore($childData['price'], $storeId, true, false);
                            } else {
                                $result[$productId]['savePrice'] = '';
                                $result[$productId]['mainPrice'] = '';
                            }
                        }
                    }
                }
            }
        }
        return array_values($result);
    }

    private function attributeSelectQuery(int $storeId, array $productIds, string $attribute, string $type): array
    {
        $map = [];
        foreach ($productIds as $productId) {
            $map[$productId] = '';
        }

        $mainTable = 'catalog_product_entity_' . $type;

        foreach ([$storeId, 0, 1] as $sid) {
            $query = $this->resource->getConnection()
                ->select()
                ->from(['ev' => $this->resource->getTableName($mainTable)], ['entity_id', 'value'])
                ->where('ev.attribute_id = ?', $this->getAttributeId($attribute))
                ->where('ev.store_id = ?', $sid)
                ->where('ev.entity_id IN(?)', $productIds)
                ->order('ev.store_id')
                ->group('ev.entity_id');

            if ($this->getPkField() == 'row_id') {
                $query = $this->resource->getConnection()
                    ->select()
                    ->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['entity_id'])
                    ->joinLeft(['ev' => $this->resource->getTableName($mainTable)], 'e.row_id = ev.row_id', ['value'])
                    ->where('ev.attribute_id = ?', $this->getAttributeId($attribute))
                    ->where('ev.store_id = ?', $sid)
                    ->where('e.entity_id IN(?)', $productIds)
                    ->order('ev.store_id')
                    ->group('e.entity_id');
            }

            $data = $this->resource->getConnection()->fetchPairs($query);

            foreach ($data as $productId => $value) {
                if ($map[$productId] == '' || $map[$productId] == 'no_selection') {
                    $map[$productId] = $value;
                }
            }
        }

        return $map;
    }

    private function getAttributeId(string $attributeCode): int
    {
        if (count($this->attributeIds) == 0) {
            $this->attributeIds = $this->resource->getConnection()->fetchPairs(
                $this->resource->getConnection()->select()
                    ->from(['ea' => $this->resource->getTableName('eav_attribute')], ['attribute_code', 'attribute_id'])
                    ->joinLeft(['eet' => $this->resource->getTableName('eav_entity_type')], 'eet.entity_type_id = ea.entity_type_id', [])
                    ->where('eet.entity_type_code = ?', 'catalog_product')
            );
        }

        return (int)$this->attributeIds[$attributeCode];
    }

    private function getPkField(): string
    {
        if ($this->pkField == '') {
            $this->pkField = (string)$this->resource->getConnection()->getAutoIncrementField($this->resource->getTableName('catalog_product_entity'));
        }

        return $this->pkField;
    }

    /**
     * @param $parentId
     * @return array
     */
    private function checkAndGetAllChildProductIds($parentId)
    {
        return $this->resource->getConnection()->fetchAll(
            $this->resource->getConnection()->select()
                ->from(['cpsl' => $this->resource->getTableName('catalog_product_super_link')], ['product_id','parent_id'])
                ->where('cpsl.parent_id = ?', $parentId)
        );
    }
}
