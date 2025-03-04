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

namespace Bss\HidePrice\Plugin\Helper;

/**
 * Class HidePriceEmail
 *
 * @package Bss\HidePrice\Plugin\Helper
 */
class HidePriceEmail
{
    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * EmailCartHidePrice constructor.
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
     * @param \Bss\QuoteExtension\Helper\HidePriceEmail $subject
     * @param \Closure $proceed
     * @param int $parentProductId
     * @param mixed $childProductSku
     * @param mixed $customerGroupId
     * @param mixed $storeId
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundCanShowPrice(
        \Bss\QuoteExtension\Helper\HidePriceEmail $subject,
        \Closure $proceed,
        $parentProductId,
        $childProductSku = false,
        $customerGroupId = false,
        $storeId = null,
        $quote = null
    ) {
        try {
            $parentProduct = $this->productRepository->getById($parentProductId, false, $storeId);
        } catch (\Exception $e) {
            return $proceed($parentProductId, $childProductSku);
        }
        // If quote was submitted by admin then show price
        if ($quote) {
            if ($quote->getIsAdminSubmitted()) {
                return true;
            }
        }

        if ($this->helper->activeHidePrice($parentProduct, null, false, $customerGroupId)) {
            if ($this->helper->hidePriceActionActive($parentProduct) != 2) {
                return false;
            }
        } else {
            try {
                $childProduct = $this->productRepository->get($childProductSku, false, $storeId);
            } catch (\Exception $e) {
                return $proceed($parentProductId, $childProductSku);
            }
            if ($this->helper->activeHidePrice($childProduct, null, false, $customerGroupId)
                && $this->helper->hidePriceActionActive($childProduct) != 2
            ) {
                return false;
            }
        }
        return $proceed($parentProductId, $childProductSku);
    }
}
