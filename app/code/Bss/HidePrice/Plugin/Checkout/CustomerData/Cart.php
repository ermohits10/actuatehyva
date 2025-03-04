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
namespace Bss\HidePrice\Plugin\Checkout\CustomerData;

/**
 * Class Cart
 *
 * @package Bss\HidePrice\Plugin\Checkout\CustomerData
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Cart
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\HidePrice\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * Cart constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
     * @param \Bss\HidePrice\Helper\Data $helper
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->cartHidePrice = $cartHidePrice;
    }

    /**
     * @param \BMagento\Checkout\CustomerData\Cart $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $items = $this->getQuote()->getAllVisibleItems();
        if (is_array($result['items'])) {
            $itemHidePrice = [];
            $showSubtotal = true;
            foreach ($items as $item) {
                if ($item->getProductType() == 'configurable') {
                    $parentProductId = $item->getProductId();
                    $childProductSku = $item->getSku();
                    $canShowPrice = $this->cartHidePrice->canShowPrice($parentProductId, $childProductSku);
                } else {
                    $canShowPrice = $this->cartHidePrice->canShowPrice($item->getProductId(), false);
                }
                if (!$canShowPrice) {
                    $itemHidePrice[] = $item->getId();
                }
            }

            foreach ($result['items'] as $key => $itemAsArray) {
                if (!in_array($result['items'][$key]['item_id'], $itemHidePrice)) {
                    continue;
                }
                if (isset($result['items'][$key]['product_price'])) {
                    $showSubtotal = false;
                    $result['items'][$key]['product_price'] = '';
                }
                if (isset($result['items'][$key]['product_price_value'])) {
                    if (is_array($result['items'][$key]['product_price_value'])) {
                        if (isset($result['items'][$key]['product_price_value']['incl_tax'])) {
                            $result['items'][$key]['product_price_value']['incl_tax'] = 0;
                        }
                        if (isset( $result['items'][$key]['product_price_value']['excl_tax'])) {
                            $result['items'][$key]['product_price_value']['excl_tax'] = 0;
                        }
                    } else {
                        $result['items'][$key]['product_price_value'] = 0;
                    }
                }
            }
            if (!$showSubtotal) {
                $result['subtotalAmount'] = 0;
                $result['subtotal'] = '';
                if (isset($result['subtotal_excl_tax'])) {
                    $result['subtotal_excl_tax'] = '';
                    $result['subtotal_incl_tax'] = '';
                }
            }
        }
        return $result;
    }

    /**
     * @return \Magento\Quote\Model\Quote|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
}
