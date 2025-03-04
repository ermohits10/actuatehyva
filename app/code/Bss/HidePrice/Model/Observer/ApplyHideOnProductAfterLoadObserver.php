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
 * Class ApplyHideOnProductAfterLoadObserver
 *
 * @package Bss\HidePrice\Model\Observer
 */
class ApplyHideOnProductAfterLoadObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * ApplyHideOnProductAfterLoadObserver constructor.
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * Apply hide price after load product
     *
     * @param EventObserver $observer
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($this->helper->activeHidePrice($product)) {
            $product->setDisableAddToCart(true);
            if ($this->helper->hidePriceActionActive($product) != 2) {
                if ($product->getTypeId() == 'bundle') {
                    $product->setCanShowBundlePrice(false);
                } elseif ($product->getTypeId() == 'downloadable') {
                    $product->setCanShowPrice(false);
                    $product->setLinksPurchasedSeparately(false);
                } elseif ($product->getTypeId() == 'configurable') {
                    $product->setCanShowPrice(false);
                    $this->processHidePriceConfigurable($product);
                } else {
                    $product->setCanShowPrice(false);
                }
            }
        }
        $hidePriceChildIds = $this->registry->registry('hideprice_childs_ids');
        if (is_array($hidePriceChildIds) && in_array($product->getId(), $hidePriceChildIds)) {
            $product->setCanShowPrice(false);
        }

        if (!$product->getDisableAddToCart()) {
            $product->setDisableAddToCart(false);
        }
        if ($product->getCanShowPrice() !== false) {
            $product->setCanShowPrice(true);
        }
        return $this;
    }

    /**
     * Compile with grid view bss module.
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function processHidePriceConfigurable($product)
    {
        $childIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $this->registry->unregister('hideprice_childs_ids');
        $this->registry->register('hideprice_childs_ids', $childIds[0]);
    }
}
