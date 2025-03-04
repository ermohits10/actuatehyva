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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ApplyHideOnCollectionAfterLoadObserver
 *
 * @package Bss\HidePrice\Model\Observer
 */
class ApplyHideOnCollectionAfterLoadObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * ProductRepositoryInterface
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * ApplyHideOnCollectionAfterLoadObserver constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->coreRegistry = $registry;
    }

    /**
     * Apply hide price on product collection
     *
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $childIds = $this->getChildIds();
        foreach ($collection as $product) {
            $currentProduct = $this->coreRegistry->registry('product');
            if (in_array($product->getId(), $childIds)
                && $this->helper->activeHidePrice($currentProduct)) {
                if ($currentProduct->getTypeId() == 'bundle') {
                    if ($this->helper->activeHidePrice($currentProduct)) {
                        $product->setDisableAddToCart(true);
                        $product->setIsInCollection(true);
                        $product->setIsChild(true);
                        if ($this->helper->hidePriceActionActive($currentProduct) != 2) {
                            $product->setCanShowPrice(false);
                        }
                    }
                } else {
                    $product->setDisableAddToCart($currentProduct->getDisableAddToCart());
                    $product->setIsInCollection(true);
                    $product->setIsChild(true);
                    $product->setCanShowPrice($currentProduct->getCanShowPrice());
                }
                continue;
            }
            // $sku = $product->getSku();
            // $productRepository = $this->productRepository->get($sku);
            if ($this->helper->activeHidePrice($product)) {
                $product->setDisableAddToCart(true);
                $product->setIsInCollection(true);
                if ($this->helper->hidePriceActionActive($product) != 2) {
                    $product->setCanShowPrice(false);
                }
            }
            if (!$product->getDisableAddToCart()) {
                $product->setDisableAddToCart(false);
            }
            if ($product->getCanShowPrice() !== false) {
                $product->setCanShowPrice(true);
            }
        }
        return $this;
    }

    /**
     * Get child ids of curent product
     *
     * @return array
     */
    protected function getChildIds()
    {
        $childIds = [];
        if ($currentProduct = $this->coreRegistry->registry('product')) {
            switch ($currentProduct->getTypeId()) {
                case 'grouped':
                case 'configurable':
                case 'bundle':
                    $arrays = $currentProduct->getTypeInstance()->getChildrenIds($currentProduct->getId());
                    foreach ($arrays as $array) {
                        $childIds = array_merge($childIds, array_values($array));
                    }
                    break;
                default:
                    break;
            }
        }
        return $childIds;
    }
}
