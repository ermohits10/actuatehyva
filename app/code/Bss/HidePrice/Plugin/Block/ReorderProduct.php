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
namespace Bss\HidePrice\Plugin\Block;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class ReorderProduct
 *
 * @package Bss\HidePrice\Plugin\Block
 */
class ReorderProduct
{
    /**
     * Data
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    protected $hidePriceSkus;

    /**
     * ReorderProduct constructor.
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }

    /**
     * Hide reorder button if product hide price
     *
     * @param \Bss\ReorderProduct\Block\ReorderProduct $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCanShowButtonReorder($subject, callable $proceed, $item)
    {
        if ($this->hidePriceSkus && ($this->hidePriceSkus == $item->getSku())) {
            return false;
        }
        return $proceed($item);
    }

    /**
     * Hide price if product hide price
     *
     * @param \Bss\ReorderProduct\Block\ReorderProduct $subject
     * @param callable $proceed
     * @param float $amount
     * @param int $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundFormatPrice($subject, callable $proceed, $amount, $store)
    {
        $item = $subject->getItem();
        if (!$item) {
            return $proceed($amount, $store);
        }
        $product = $item->getProduct();
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $product = $this->productRepository->get($item->getSku());
        }
        if ($this->helper->activeHidePrice($product)) {
            $this->hidePriceSkus = $item->getSku();
            return "";
        }

        return $proceed($amount, $store);
    }
}
