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
namespace Bss\HidePrice\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetHidePriceProductItem
 *
 * @package Bss\HidePrice\Model\Observer
 */
class SetHidePriceProductItem implements ObserverInterface
{
    /**
     * ProductRepositoryInterface
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Helper
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * ApplyHideOnProductAfterLoadObserver constructor.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();
        try {
            if ($quoteItem->getNeedCheckPrice()) {
                $productRepository = $this->productRepository->getById($product->getId());
                if ($this->helper->activeHidePrice($productRepository)
                    && $this->helper->hidePriceActionActive($productRepository) != 2
                ) {
                    $product->setCanShowPrice(false);
                }
            }
            if ($quoteItem->getProductType() == 'grouped') {
                $buyRequest = $quoteItem->getBuyRequest()->getData();
                if (isset($buyRequest['super_product_config']['product_id'])) {
                    $parentProductId = $buyRequest['super_product_config']['product_id'];
                    $parentProduct = $this->productRepository->getById($parentProductId);
                    $product->setCanShowPrice(null);
                    if ($this->helper->activeHidePrice($parentProduct)
                        && $this->helper->hidePriceActionActive($parentProduct) != 2
                    ) {
                        $product->setCanShowPrice(false);
                    }
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this;
        }
        return $this;
    }
}
