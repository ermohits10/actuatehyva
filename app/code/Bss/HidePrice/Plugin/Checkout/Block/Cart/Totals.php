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
namespace Bss\HidePrice\Plugin\Checkout\Block\Cart;

/**
 * Class Totals
 *
 * @package Bss\HidePrice\Plugin\Checkout\Block\Cart
 */
class Totals
{
    /**
     * @var \Bss\HidePrice\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * Totals constructor.
     * @param \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
     */
    public function __construct(
        \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
    ) {
        $this->cartHidePrice = $cartHidePrice;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Totals $subject
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsLayout(
        \Magento\Checkout\Block\Cart\Totals $subject,
        $result
    ) {
        $quote = $subject->getQuote();
        if (!$this->canShowTotal($quote)) {
            $result = json_decode($result, JSON_HEX_TAG);
            unset($result['components']['block-totals']);
            $result = json_encode($result, JSON_HEX_TAG);
        }
        return $result;
    }

    /**
     * Can show subtotal
     *
     * @param \Magento\Framework\DataObject $total
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function canShowTotal($quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'configurable') {
                $parentProductId = $item->getProductId();
                $childProductSku = $item->getSku();
                $canShowPrice = $this->cartHidePrice->canShowPrice($parentProductId, $childProductSku);
            } else {
                $canShowPrice = $this->cartHidePrice->canShowPrice($item->getProductId(), false);
            }
            if (!$canShowPrice) {
                return false;
            }
        }
        return true;
    }
}
