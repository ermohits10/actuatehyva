<?php

namespace Actuate\ProductAttribute\ViewModel;

use Magento\Catalog\Helper\Output;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductAttributeMappingViewModel implements ArgumentInterface
{
    private ScopeConfigInterface $scopeConfig;
    private Json $json;
    private ResourceConnection $resourceConnection;
    private Output $catalogOutputHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param ResourceConnection $resourceConnection
     * @param Output $catalogOutputHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json,
        ResourceConnection $resourceConnection,
        Output $catalogOutputHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
        $this->catalogOutputHelper = $catalogOutputHelper;
    }

    /**
     * @return array
     */
    public function fetchProductAttributeMapping(): array
    {
        $productAttributes = [];
        if ($mapConfig = $this->scopeConfig->getValue('actuate_product_attribute_manage/general/attribute_mapping')) {
            $mapConfigArr = $this->json->unserialize($mapConfig);
            foreach ($mapConfigArr as $mapConfigItem) {
                $productAttributes[$mapConfigItem['section_name']][] = $mapConfigItem['attribute_code'];
            }
        }
        return $productAttributes;
    }

    public function getSpecificationByAttributeMapping($attributes) {
        $specifications = [];
        if ($attributes) {
            $mappingAttribute = $this->fetchProductAttributeMapping();
            foreach ($attributes as $attribute) {
                if ($group = $this->checkAndGetAttrbiuteMapByCode($attribute['code'], $mappingAttribute)) {
                    $specifications[$group][] = $attribute;
                } else {
                    $specifications['Others'][] = $attribute;
                }
            }
        }

        return $specifications;
    }

    public function checkAndGetAttrbiuteMapByCode($code, $mappingAttribute) {
        foreach ($mappingAttribute as $key => $attrbiutes) {
            if (in_array($code, $attrbiutes)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * @param $attrCode
     * @param null $optionId
     * @param int $toolTipFor // 1: Option, 2: Attribute
     * @return mixed|string|null
     */
    public function getTooltip($attrCode, $optionId = null, int $toolTipFor = 1)
    {
        $toolTipText = null;
        $connection = $this->resourceConnection->getConnection();
        $sql = $connection->select()
            ->from(['mst_opt' => $this->resourceConnection->getTableName('mst_navigation_attribute_config')], ['config'])
            ->where('attribute_code = ?', $attrCode);
        $result = $connection->fetchRow($sql);
        if ($result && !empty($result['config'])) {
            if ($toolTipFor === 1 && !empty($optionId)) {
                $config = $this->json->unserialize($result['config']);

                $options = $config['options'] ?? [];
                $currentOption = array_values(array_filter($options, function ($option) use ($optionId) {
                    return (int)$option['option_id'] === (int) $optionId;
                }));

                if (!empty($currentOption)) {
                    $toolTipText = $currentOption[0]['label'] ?? null;
                }
            } elseif ($toolTipFor === 2) {
                $config = $this->json->unserialize($result['config']);
                $toolTipText = $config['tooltip'] ?? null;
            }
        }

        return $toolTipText;
    }

    /**
     * @param $attributes
     * @return array
     */
    private function getAttributeByGroup($attributes)
    {
        $allAttributes = $this->getSpecificationByAttributeMapping($attributes);
        $attributeByGroup = [];
        foreach ($allAttributes as $group => $attributeData) {
            $attributeByGroup[$group] = array_column($attributeData, 'code', 'label');
        }

        return $attributeByGroup;
    }

    /**
     * @param $product
     * @param $attributes
     * @return array
     * @throws LocalizedException
     */
    public function getAllConfigurableOptionAttributes($product, $attributes)
    {
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        $productAttributeOptions = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        $attributeByGroup = $this->getAttributeByGroup($attributes);
        $mappingAttribute = $this->fetchProductAttributeMapping();
        foreach ($productAttributeOptions as $attributeOption) {
            if ($group = $this->checkAndGetAttrbiuteMapByCode($attributeOption['attribute_code'], $mappingAttribute)) {
                $attributeByGroup[$group][$attributeOption['label']] = $attributeOption['attribute_code'];
            } else {
                $attributeByGroup['Others'][$attributeOption['label']] = $attributeOption['attribute_code'];
            }
        }

        $configOptionAttribute = [];
        foreach ($childProducts as $childProduct) {
            foreach ($attributeByGroup as $group => $attributeList) {
                foreach ($attributeList as $label => $attribute) {
                    $productAttrValue = $childProduct->getResource()->getAttribute($attribute)->getFrontend()->getValue($childProduct);
                    $attributeValue = $this->catalogOutputHelper->productAttribute($childProduct, $productAttrValue, $attribute);
                    $configOptionAttribute[$childProduct->getId()][$group][] = [
                        "value" => $attributeValue,
                        "code" => $attribute,
                        "label" => $label
                    ];
                }
            }
        }

        return $configOptionAttribute;
    }
}
