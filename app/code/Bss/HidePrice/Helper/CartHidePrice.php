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
namespace Bss\HidePrice\Helper;

/**
 * Class CartHidePrice
 *
 * @package Bss\HidePrice\Plugin\Helper
 */
class CartHidePrice extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * CartHidePrice constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Bss\HidePrice\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isChildProductHidePrice($item)
    {
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
            if ($this->helper->activeHidePrice($childProduct, null, true)) {
                return true;
            }
        } else {
            return false;
        }

        return false;
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

    /**
     * @param int $parentProductId
     * @param mixed $childProductSku
     * @param bool $buttonCheckout
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function canShowPrice($parentProductId, $childProductSku, $buttonCheckout = false)
    {
        try {
            $parentProduct = $this->productRepository->getById($parentProductId);
        } catch (\Exception $e) {
            return false;
        }
        if (!$childProductSku) {
            if ($parentProduct->getCanShowPrice() === false) {
                return false;
            }
        } else {
            if ($parentProduct->getDisableAddToCart()) {
                if ($buttonCheckout) {
                    return false;
                }
                if ($parentProduct->getCanShowPrice() === false) {
                    return false;
                }
            } else {
                try {
                    $childProduct = $this->productRepository->get($childProductSku);
                } catch (\Exception $e) {
                    return false;
                }
                if ($buttonCheckout && $childProduct->getDisableAddToCart()) {
                    return false;
                }
                if ($childProduct->getCanShowPrice() === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getDisableCheckout()
    {
        $default = ['checkout_index_index', 'onestepcheckout_index_index'];
        $disableCheckoutControllerList = $this->helper->getDisableCheckout();
        $disableCheckoutControllerList = str_replace(' ', '', $disableCheckoutControllerList);
        $disableCheckoutControllerList = explode(',', $disableCheckoutControllerList);
        return array_merge($default, $disableCheckoutControllerList);
    }

    /**
     * Is place order
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @retrun bool
     */
    public function isPlaceOrder($quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'configurable') {
                $parentProductId = $item->getProductId();
                $childProductSku = $item->getSku();
                $canShowPrice = $this->canShowPrice($parentProductId, $childProductSku, true);
            } else {
                $canShowPrice = $this->canShowPrice($item->getProductId(), false, true);
            }
            if (!$canShowPrice) {
                return false;
            }
        }
        return true;
    }
}
