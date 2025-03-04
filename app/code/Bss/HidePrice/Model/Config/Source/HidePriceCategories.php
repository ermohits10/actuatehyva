<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class HidePriceCategories
 *
 * @package Bss\HidePrice\Model\Config\Source
 */
class HidePriceCategories implements ArrayInterface
{
    /**
     * CategoryFactory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * CollectionFactory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * HidePriceCategories constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    /**
     * To Option Array
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];
        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }

    /**
     * To Array
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function toArray()
    {
        $categories = $this->getCategoryCollection(true, false, false, false);
        $catagoryList = [];
        foreach ($categories as $category) {
            $catagoryList[$category->getEntityId()] = __(
                $this->getParentName($category->getPath())
                . $category->getName() . '(' . $category->getEntityId() . ')'
            );
        }
        return $catagoryList;
    }

    /**
     * Get Parent Name
     *
     * @param string $path
     * @return string
     */
    private function getParentName($path = '')
    {
        $parentName = '';
        $rootCats = [1,2];
        $catTree = explode("/", $path);
        array_pop($catTree);
        if ($catTree && (count($catTree) > count($rootCats))) {
            foreach ($catTree as $catId) {
                if (!in_array($catId, $rootCats)) {
                    $category = $this->loadCatId($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }
        return $parentName;
    }

    /**
     * Load CatId
     *
     * @param int $catId
     * @return \Magento\Catalog\Model\Category
     */
    private function loadCatId($catId)
    {
        return $this->categoryFactory->create()->load($catId);
    }
}
