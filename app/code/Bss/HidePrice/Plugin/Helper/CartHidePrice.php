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
 * Class CartHidePrice
 *
 * @package Bss\HidePrice\Plugin\Helper
 */
class CartHidePrice
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * CartHidePrice constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Bss\QuoteExtension\Helper\CartHidePrice $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundIsChildProductHidePrice(
        \Bss\QuoteExtension\Helper\CartHidePrice $subject,
        \Closure $proceed,
        $item
    ) {
        $product = $item->getProduct();
        if ($product->getTypeId() == 'bundle') {
            $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
            $bundleOptionsIds = $optionsQuoteItemOption
                ? $this->serializer->unserialize($optionsQuoteItemOption->getValue())
                : [];
            if ($bundleOptionsIds) {
                $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');
                $bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());
                $selectionsCollection = $product->getTypeInstance()->getSelectionsByIds($bundleSelectionIds, $product);
                $childProductIds = [];
                foreach ($selectionsCollection as $selection) {
                    $childProductIds[] = $selection->getId();
                }
                $collection = $this->getProductCollection($childProductIds);
                $hidePrice = false;
                foreach ($collection as $itemChild) {
                    if ($itemChild->getCanShowPrice() === false) {
                        $hidePrice = true;
                        break;
                    }
                }
                return $hidePrice;
            }
        } elseif ($product->getTypeId() == 'configurable') {
            $childProduct = $item->getOptionByCode('simple_product')->getProduct();
            if ($childProduct->getCanShowPrice() === false) {
                return true;
            }
        } else {
            return false;
        }
        return $proceed($item);
    }

    /**
     * Check visible of price
     *
     * @param \Bss\QuoteExtension\Helper\CartHidePrice $subject
     * @param \Closure $proceed
     * @param int $parentProductId
     * @param mixed $childProductSku
     * @param mixed $quote
     * @return bool|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundCanShowPrice(
        \Bss\QuoteExtension\Helper\CartHidePrice $subject,
        \Closure $proceed,
        $parentProductId,
        $childProductSku,
        $quote = null
    ) {
        try {
            $parentProduct = $this->productRepository->getById($parentProductId);
        } catch (\Exception $e) {
            return $proceed($parentProductId, $childProductSku);
        }
        // If quote was submitted by admin then show price
        if ($quote) {
            if ($quote->getIsAdminSubmitted()) {
                return true;
            }
        }
        if (!$childProductSku) {
            if ($parentProduct->getCanShowPrice() === false || $parentProduct->getCanShowBundlePrice() === false) {
                return false;
            }
        } else {
            if ($parentProduct->getDisableAddToCart()) { // parent enable
                if ($parentProduct->getCanShowPrice() === false || $parentProduct->getCanShowBundlePrice() === false) {
                    return false;
                }
            } else { // parent disable
                try {
                    $childProduct = $this->productRepository->get($childProductSku);
                } catch (\Exception $e) {
                    return $proceed($parentProductId, $childProductSku);
                }
                if ($childProduct->getCanShowPrice() === false) {
                    return false;
                }
            }
        }
        return $proceed($parentProductId, $childProductSku);
    }

    /**
     * @param array $ids
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    protected function getProductCollection($ids)
    {
        return $this->productCollectionFactory->create()->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $ids]);
    }
}
