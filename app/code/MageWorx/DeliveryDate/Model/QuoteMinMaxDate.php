<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model;

use Magento\Quote\Api\Data\CartInterface;
use MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface;
use MageWorx\DeliveryDate\Api\DateDiapasonCacheInterface;
use MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;

class QuoteMinMaxDate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements QuoteMinMaxDateInterface
{
    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    private $quote;

    /**
     * @var \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterfaceFactory
     */
    protected $dateDiapasonFactory;

    /**
     * @var DateDiapasonCacheInterface
     */
    protected $dateDiapasonCache;

    /**
     * Collection Zend Db select
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $select;

    /**
     * Attribute cache
     *
     * @var array
     */
    protected $attributesCache = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * QuoteMinMaxDate constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterfaceFactory $dateDiapasonFactory
     * @param DateDiapasonCacheInterface $dateDiapasonCache
     * @param \Magento\Quote\Model\Quote|null $quote
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterfaceFactory $dateDiapasonFactory,
        \MageWorx\DeliveryDate\Api\DateDiapasonCacheInterface $dateDiapasonCache,
        \Magento\Quote\Model\Quote $quote = null,
        $connectionName = null
    ) {
        $this->dateDiapasonFactory = $dateDiapasonFactory;
        $this->dateDiapasonCache   = $dateDiapasonCache;
        $this->quote               = $quote;
        $this->productResource     = $productResource;

        parent::__construct($context, $connectionName);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * @inheritDoc
     */
    public function getDiapason(): DateDiapasonInterface
    {
        $cacheKey      = $this->getCacheKey();
        $cachedVersion = $this->dateDiapasonCache->get($cacheKey);
        if ($cachedVersion) {
            return $cachedVersion;
        }

        $quote   = $this->getQuote();
        $storeId = (int)$quote->getStoreId();

        $productIds = $this->getUsedProductIdsFromQuote($quote);

        $minDate = $this->getMinDateForProductByIdFromDB($productIds, $storeId);
        $maxDate = $this->getMaxDateForProductByIdFromDB($productIds, $storeId);

        /** @var \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface $diapason */
        $diapason = $this->dateDiapasonFactory->create();
        $diapason->setMinDate($minDate)
                 ->setMaxDate($maxDate);

        $this->dateDiapasonCache->set($cacheKey, $diapason);

        return $diapason;
    }

    /**
     * @inheritDoc
     */
    public function setQuote(CartInterface $quote): QuoteMinMaxDateInterface
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQuote(): ?CartInterface
    {
        return $this->quote;
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        $quote      = $this->getQuote();
        $storeId    = $quote->getStoreId();
        $productIds = [];
        $items      = $quote->getAllItems();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $productIds[] = $item->getProduct()->getId();
        }

        sort($productIds);

        $key = 'store_' . $storeId . ':products_' . implode('-', $productIds);

        return $key;
    }

    /**
     * @param string $attributeCode
     * @param array $productIds
     * @param int $storeId
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributeValues(string $attributeCode, array $productIds, int $storeId): ?array
    {
        $connection = $this->getConnection();

        $this->select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            []
        )->where('e.entity_id IN (?)', $productIds);

        $this->_joinAttribute($storeId, $attributeCode, $attributeCode);

        $values = array_filter($this->getConnection()->fetchCol($this->select));

        return $values;
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @return \DateTimeInterface|null
     */
    protected function getMinDateForProductByIdFromDB(array $productIds, int $storeId): ?\DateTimeInterface
    {
        $value = null;

        if (empty($productIds)) {
            return $value;
        }

        $attributeCode = QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_FROM;

        $values = $this->getAttributeValues($attributeCode, $productIds, $storeId);

        if (!empty($values)) {
            $value = max($values);
            if ($value) {
                $value = new \DateTime($value);
            }
        }

        return $value;
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @return \DateTimeInterface|null
     */
    protected function getMaxDateForProductByIdFromDB(array $productIds, int $storeId): ?\DateTimeInterface
    {
        $value = null;

        if (empty($productIds)) {
            return $value;
        }

        $attributeCode = QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_TO;
        $values        = $this->getAttributeValues($attributeCode, $productIds, $storeId);

        if (!empty($values)) {
            $value = min($values);
            if ($value) {
                $value = new \DateTime($value);
            }
        }

        return $value;
    }

    /**
     * @param \Magento\Quote\Model\Quote|null $quote
     * @return array
     */
    protected function getUsedProductIdsFromQuote(\Magento\Quote\Model\Quote $quote = null): array
    {
        if (!$quote) {
            $quote = $this->getQuote();
        }

        $quoteItems = $quote->getAllItems();
        $productIds = [];
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getChildren()) {
                $parentProduct = $quoteItem->getProduct();
                if ($parentProduct->getTypeId() === 'configurable') {
                    $children     = $quoteItem->getChildren();
                    $child        = reset($children);
                    $productIds[] = $child->getProduct()->getId();
                } elseif ($parentProduct->getTypeId() === 'bundle') {
                    $children = $quoteItem->getChildren();
                    foreach ($children as $child) {
                        $productIds[] = $child->getProduct()->getId();
                    }
                }
            } else {
                $productIds[] = $quoteItem->getProduct()->getId();
            }
        }

        return $productIds;
    }

    /**
     * Join attribute by code
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param string $column Add attribute value to given column
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _joinAttribute($storeId, $attributeCode, $column = null)
    {
        $connection     = $this->getConnection();
        $attribute      = $this->_getAttribute($attributeCode);
        $linkField      = $this->productResource->getLinkField();
        $attrTableAlias = 't1_' . $attributeCode;
        $this->select->joinLeft(
            [$attrTableAlias => $attribute['table']],
            "e.{$linkField} = {$attrTableAlias}.{$linkField}"
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.store_id = ?', Store::DEFAULT_STORE_ID)
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.attribute_id = ?', $attribute['attribute_id']),
            []
        );
        // Global scope attribute value
        $columnValue = 't1_' . $attributeCode . '.value';

        if (!$attribute['is_global']) {
            $attrTableAlias2 = 't2_' . $attributeCode;
            $this->select->joinLeft(
                ['t2_' . $attributeCode => $attribute['table']],
                "{$attrTableAlias}.{$linkField} = {$attrTableAlias2}.{$linkField}"
                . ' AND ' . $attrTableAlias . '.attribute_id = ' . $attrTableAlias2 . '.attribute_id'
                . ' AND ' . $connection->quoteInto($attrTableAlias2 . '.store_id = ?', $storeId),
                []
            );
            // Store scope attribute value
            $columnValue = $this->getConnection()->getIfNullSql('t2_' . $attributeCode . '.value', $columnValue);
        }

        // Add attribute value to result set if needed
        if (isset($column)) {
            $this->select->columns(
                [
                    $column => $columnValue
                ]
            );
        }
    }

    /**
     * Get attribute data by attribute code
     *
     * @param string $attributeCode
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getAttribute($attributeCode)
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->productResource->getAttribute($attributeCode);

            $this->attributesCache[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id'   => $attribute->getId(),
                'table'          => $attribute->getBackend()->getTable(),
                'is_global'      => $attribute->getIsGlobal() == ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend_type'   => $attribute->getBackendType(),
            ];
        }

        return $this->attributesCache[$attributeCode];
    }
}
