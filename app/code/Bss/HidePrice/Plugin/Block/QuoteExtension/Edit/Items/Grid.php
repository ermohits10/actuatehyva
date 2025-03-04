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
namespace Bss\HidePrice\Plugin\Block\QuoteExtension\Edit\Items;

/**
 * Class Grid
 *
 * @package Bss\HidePrice\Plugin\Block\QuoteExtension\Edit\Items
 */
class Grid
{
    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * Grid constructor.
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->stockState = $stockState;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * @param \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Items\Grid $subject
     * @param $result
     * @return \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Item[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterGetItems(
        \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Items\Grid $subject,
        $result
    ) {
        $items = $result;
        $customerGroupId = $subject->getQuote()->getCustomerGroupId();
        foreach ($items as $item) {
            $storeId = $item->getStoreId();
            $item->setCanShowPrice(true);
            if ($item->getProductType() == 'configurable') {
                $parentProductId = $item->getProductId();
                $childProductSku = $item->getSku();
                $parentProduct = $this->productRepository->getById($parentProductId, false, $storeId);
                if (!$parentProduct) {
                    continue;
                }
                if ($this->helper->activeHidePrice($parentProduct, $storeId, false, $customerGroupId)
                    && $this->helper->hidePriceActionActive($parentProduct) != 2
                ) {
                    $item->setCanShowPrice(false);
                } else {
                    $childProduct = $this->productRepository->get($childProductSku, false, $storeId);
                    if (!$childProduct) {
                        continue;
                    }
                    if ($this->helper->activeHidePrice($childProduct, $storeId, false, $customerGroupId)
                        && $this->helper->hidePriceActionActive($childProduct) != 2
                    ) {
                        $item->setCanShowPrice(false);
                    }
                }
            } else {
                $product = $this->productRepository->getById($item->getProductId(), false, $storeId);
                if (!$product) {
                    continue;
                }
                if ($this->helper->activeHidePrice($product, $storeId, false, $customerGroupId)
                    && $this->helper->hidePriceActionActive($product) != 2
                ) {
                    $item->setCanShowPrice(false);
                }
            }
        }
        return $items;
    }
}
