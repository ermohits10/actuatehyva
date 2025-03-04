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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin;

use Bss\HidePrice\Helper\Data;
use Magento\Catalog\Test\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts\Product;
use Magento\Framework\App\Request\Http;

/**
 * Class CategoryHidePrice
 *
 * @package Bss\HidePrice\Plugin
 */
class CategoryHidePrice
{
    /**
     * Data
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
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Http
     */
    protected $request;

    /**
     * CategoryHidePrice constructor.
     *
     * @param Http $request
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Http $request,
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\PriceBox $subject
     * @param mixed $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterToHtml($subject, $result)
    {
        $product = $subject->getSaleableItem();
        $productHidePriceActionActive = $this->helper->hidePriceActionActive($product);
        if ($product->getTypeId() == "bundle") {
            if ($this->helper->activeHidePrice($product)
                && $this->request->getFullActionName() != 'catalog_product_view') {
                if (
                    $productHidePriceActionActive == Data::USER_GLOBAL_CONFIG
                    && $this->helper->getHidePriceAction() != Data::SHOW_PRICE_HIDE_ADD_2_CART
                    || $productHidePriceActionActive != Data::SHOW_PRICE_HIDE_ADD_2_CART
                ) {
                    return $this->addHidePriceText($result, $product);
                }
                $additionalHtml = '<div class="hide_price_text hide_price_text_'.$product->getId().'">'
                    . $this->helper->getHidepriceMessage($product).'</div>
                <script type="text/javascript">
                    require(["jquery"], function($){
                        $(".product-item").trigger("contentUpdated");
                    });
                </script>
                <script type="text/x-magento-init">
                    {
                        ".hide_price_text_'.$product->getId().'": {
                            "Bss_HidePrice/js/hide_price": {
                                "selector" : "'.$this->helper->getSelector().'",
                                "showPrice" : true
                            }
                        }
                    }
                </script>';

                return $result . $additionalHtml;
            }
            if (
                $productHidePriceActionActive == Data::USER_GLOBAL_CONFIG
                && $this->helper->getHidePriceAction() != Data::SHOW_PRICE_HIDE_ADD_2_CART
                || $this->helper->activeHidePrice($product)
                && $productHidePriceActionActive != Data::SHOW_PRICE_HIDE_ADD_2_CART
            ) {
                return '';
            }
            return $result;
        }
        $productRepository = $this->productRepository->get($product->getSku());
        $childOfGroup = false;
        if ($currentProduct = $this->registry->registry('product')) {
            if ($currentProduct->getId() == $product->getId()
                || $currentProduct->getTypeId() != 'grouped'
                || $childOfGroup = $this->isChildOfGroupedProduct($currentProduct, $productRepository)
            ) {
                $productActiveHidePrice = $this->helper->activeHidePrice($productRepository);
                $productHidePriceActionActive = $this->helper->hidePriceActionActive($productRepository);
                if (!$this->helper->activeHidePrice($currentProduct)
                    && $childOfGroup
                    && $subject->getPrice()->getPriceCode() == 'final_price'
                    && $productActiveHidePrice
                ) {
                    $html = '<div class="hide_price_text hide_price_text_'.$productRepository->getId().'">'
                        . $this->helper->getHidepriceMessage($productRepository).'</div>';
                    if ($productHidePriceActionActive == Data::HIDE_PRICE_ADD_2_CART) {
                        $result = $html;
                    } else if ($productHidePriceActionActive == Data::SHOW_PRICE_HIDE_ADD_2_CART) {
                        $result = $result . $html;
                    }
                    return $result;
                }
                if ($productRepository->getTypeId() == 'configurable' && $productActiveHidePrice) {
                    $element = ".product-item-details [data-product-sku='". $product->getSku() . "']";
                    $result .= '<script type="text/x-magento-init">
                                {
                                    "'.$element.'": {
                                        "Bss_HidePrice/js/hide_cart": {
                                            "selector" : "'.$this->helper->getSelector().'",
                                            "disableCart" : "true"
                                        }
                                    }
                                }
                            </script>';
                    return $result;
                }
                if (!$childOfGroup
                    && $productActiveHidePrice
                ) {
                    if ($productHidePriceActionActive == Data::HIDE_PRICE_ADD_2_CART) {
                        $result = '';
                    }
                    $singleList = ['simple', 'downloadable', 'virtual'];
                    if (in_array($productRepository->getTypeId(), $singleList)) {
                        $element = ".product-item-details [data-product-sku='". $product->getSku() . "']";
                        $result .= '<script type="text/x-magento-init">
                                {
                                    "'.$element.'": {
                                        "Bss_HidePrice/js/hide_cart": {
                                            "selector" : "'.$this->helper->getSelector().'",
                                            "disableCart" : "true"
                                        }
                                    }
                                }
                            </script>';
                    }
                    return $result;
                }

                return $result;
            }
        }
        return $this->addHidePriceText($result, $productRepository);
    }

    /**
     * @param mixed $result
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addHidePriceText($result, $product)
    {
        $selector =".action.tocart";
        if ($this->helper->getSelector()) {
            $selector = $this->helper->getSelector();
        }
        $sku = $product->getSku();
        $product = $this->productRepository->get($sku);
        if ($this->helper->activeHidePrice($product)
            || ($product->getTypeId() == 'grouped'
                && $this->helper->activeHidePriceGrouped($product))
        ) {
            $showPrice = $this->helper->hidePriceActionActive($product) == Data::SHOW_PRICE_HIDE_ADD_2_CART;
            $button = '<div class="hide_price_text hide_price_text_'.$product->getId().'">'
                . $this->helper->getHidepriceMessage($product).'</div>
                <script type="text/javascript">
                    require(["jquery"], function($){
                        $(".product-item").trigger("contentUpdated");
                    });
                </script>
                <script type="text/x-magento-init">
                    {
                        ".hide_price_text_'.$product->getId().'": {
                            "Bss_HidePrice/js/hide_price": {
                                "selector" : "'.$this->helper->getSelector().'",
                                "showPrice" : "'.$showPrice.'"
                            }
                        }
                    }
                </script>';

            return $result . $button;
        } else {
            if ($product->getTypeId() == 'configurable' && $this->helper->isEnable()) {
                $result = '<div id="hideprice_'.$product->getId().'"></div>
                            <div id="hideprice_price'.$product->getId().'">'.$result.'</div>';
            }

            $result = $result.'<div class="hideprice_show hideprice_show_'.$product->getId().'"></div>
                <script type="text/x-magento-init">
                    {
                        ".hideprice_show_'.$product->getId().'": {
                            "Bss_HidePrice/js/show_tocart": {
                                "selector" : "'.$selector.'"
                            }
                        }
                    }
                </script>';
        }
        return $result;
    }

    /**
     * @param Product $product
     * @param Product $child
     * @return mixed
     */
    private function isChildOfGroupedProduct($product, $child)
    {
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        foreach ($associatedProducts as $childProduct) {
            if ($childProduct->getId() == $child->getId()) {
                return true;
            }
        }
        return false;
    }
}
