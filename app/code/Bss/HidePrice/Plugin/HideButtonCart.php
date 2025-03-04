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
namespace Bss\HidePrice\Plugin;

use Magento\Catalog\Block\Product\View as MagentoView;

/**
 * Class HideButtonCart
 *
 * @package Bss\HidePrice\Plugin
 */
class HideButtonCart
{
    /**
     * Data
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * HideButtonCart constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Hide Add to cart Button
     *
     * @param MagentoView $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterToHtml(
        MagentoView $subject,
        $result
    ) {
        $matchedNames = [
            'product.info.addtocart.additional',
            'product.info.addtocart',
            'product.info.addtocart.bundle'
        ];
        if (in_array($subject->getNameInLayout(), $matchedNames)) {
            $product = $subject->getProduct();
            if ($this->helper->activeHidePrice($product)) {
                $pattern = '#<button([^>]*)product-addtocart-button([^*]*)<\/button>#';
                $result = preg_replace($pattern, '', $result);
                $result .= '<p id="hideprice" class="hide_price_text">'
                    . $this->helper->getHidepriceMessage($product).'</p>';
            } elseif ($product->getTypeId() == 'grouped'
                && $this->helper->activeHidePriceGrouped($product)) {
                if (!$product->getIsActiveRequest4Quote())
                    $result = '';
                $result .= '<p id="hideprice" class="hide_price_text">'
                    . $this->helper->getHidepriceMessage($product).'</p>';
            } else {
                if ($product->getTypeId() == 'configurable' && $this->helper->isEnable()) {
                    $result = '<div id="hideprice">'.$result.'</div>';
                }
            }
        }
        return $result;
    }

    /**
     * @param MagentoView $subject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeToHtml(MagentoView $subject)
    {
        $product = $subject->getProduct();
        $hidePriceMessage = $this->helper->getHidepriceMessage($product, false);
        if ($this->helper->activeHidePrice($product)) {
            $product->setHidepriceMessage($hidePriceMessage);
        }
    }
}
