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
namespace Bss\HidePrice\Plugin\Checkout\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Swatches\Helper\Data as SwatchData;

/**
 * Class Cart
 *
 * @package Bss\HidePrice\Plugin\Checkout\Model
 */
class Cart
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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SwatchData
     */
    private $swatchHelper;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    protected $configurableProductHelper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogHelper;

    /**
     * Cart constructor.
     *
     * @param SwatchData $swatchData
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\ConfigurableProduct\Helper\Data $configurableProductHelper
     * @param \Magento\Catalog\Helper\Product $catalogHelper
     * @param \Magento\Framework\Message\ManagerInterface $managerMessage
     */
    public function __construct(
        SwatchData $swatchData,
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\ConfigurableProduct\Helper\Data $configurableProductHelper,
        \Magento\Catalog\Helper\Product $catalogHelper,
        \Magento\Framework\Message\ManagerInterface $managerMessage
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configurable = $configurable;
        $this->messageManager = $managerMessage;
        $this->swatchHelper = $swatchData;
        $this->configurableProductHelper = $configurableProductHelper;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Check hide price product before add product to cart
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return array
     * @throws LocalizedException|NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        $product = $this->getProduct($productInfo);
        if (isset($requestInfo['quoteextension'])
            && $requestInfo['quoteextension'] == 1
            && $product->getIsActiveRequest4Quote()
        ) {
            return [$productInfo, $requestInfo];
        }
        $activeHidePrice = $this->helper->activeHidePrice($product);
        $hidePriceActionActive = $this->helper->hidePriceActionActive($product);
        if ($activeHidePrice && $hidePriceActionActive) {
            $dataMessage = $this->helper->getHidepriceMessageLink($product);
            if (is_array($dataMessage)) {
                $message = __(
                    '<a href="%1">' . $dataMessage["message"] . '</a>',
                    $dataMessage['link']
                );
                throw new LocalizedException(__($this->messageManager->addErrorMessage($message)));
            } else {
                throw new LocalizedException(__($dataMessage));
            }
        }
        $productType = $product->getTypeId();
        if ($productType == 'grouped'
            && (!$activeHidePrice || !$hidePriceActionActive)
            && isset($requestInfo['super_group']) && is_array($requestInfo['super_group'])
        ) {
            $itemGroupAddToCart = $requestInfo['super_group'];
            foreach ($itemGroupAddToCart as $key => $qty) {
                if ($qty > 0) {
                    $itemGroupAddToCartIds[] = $key;
                }
            }
            $collection = $this->getProductCollection($itemGroupAddToCartIds);
            $errorMessage = '';
            foreach ($collection as $item) {
                if ($this->helper->activeHidePrice($item)) {
                    $itemGroupAddToCart[$item->getId()] = 0;
                    if ($errorMessage != '') {
                        $errorMessage .= ', ';
                    }
                    $errorMessage .= $item->getName();
                }
            }
            $requestInfo['super_group'] = $itemGroupAddToCart;
            if ($errorMessage != '') {
                throw new LocalizedException(
                    __('%1 cannot be added to your cart.', $errorMessage)
                );
            }
        } elseif ($productType == 'configurable') {
            if (!isset($requestInfo['super_attribute'])) {
                if ($attributesData = $this->swatchHelper->getSwatchAttributesAsArray($product)) {
                    foreach ($attributesData as $key => $val) {
                        $requestInfo['super_attribute'][$key] = "";
                    }
                }
                if ($options = $this->configurableProductHelper
                    ->getOptions($product, $this->getAllowProducts($product))
                ) {
                    foreach ($options as $attributeId => $val) {
                        if ($attributeId !== "index") {
                            $requestInfo['super_attribute'][$attributeId] = "";
                        }
                    }
                }
            }
            $optionsAddToCart = $requestInfo['super_attribute'];
            $childProduct = $this->configurable->getProductByAttributes($optionsAddToCart, $product);
            if (!$childProduct) {
                throw new LocalizedException(__('This product does not exist.'));
            }
            if ($this->helper->activeHidePrice($childProduct)) {
                $dataMessage = $this->helper->getHidepriceMessageLink($product);
                if (is_array($dataMessage)) {
                    $message = __(
                        '<a href="%1">' . $dataMessage["message"] . '</a>',
                        $dataMessage['link']
                    );
                    throw new LocalizedException(__($this->messageManager->addErrorMessage($message)));
                } else {
                    throw new LocalizedException(__($dataMessage));
                }
            }
        } elseif ($productType == 'bundle'
            && (!$activeHidePrice || !$hidePriceActionActive)
            && isset($requestInfo['bundle_option']) && is_array($requestInfo['bundle_option'])
        ) {
            $childProductIds = [];
            $optionIds = $requestInfo['bundle_option'];
            $selectionCollection = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );
            foreach ($selectionCollection as $child) {
                if (isset($optionIds[$child->getOptionId()])
                    && $optionIds[$child->getOptionId()] == $child->getSelectionId()
                ) {
                    $childProductIds[] = $child->getId();
                }
            }
            $errorMessage = '';
            $collection = $this->getProductCollection($childProductIds);
            foreach ($collection as $item) {
                if ($this->helper->activeHidePrice($item)
                    && ($this->helper->hidePriceActionActive($item) == 2 || $this->helper->hidePriceActionActive($item) == 1)
                ) {
                    if ($errorMessage != '') {
                        $errorMessage .= ', ';
                    }
                    $errorMessage .= $item->getName();
                }
            }
            if ($errorMessage != '') {
                throw new LocalizedException(__('%1 cannot be added to your cart.', $errorMessage));
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * Get AllowProducts
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $currentProduct
     * @return Product[]|mixed
     */
    protected function getAllowProducts($currentProduct)
    {
        $products = [];
        $skipSaleableCheck = $this->catalogHelper->getSkipSaleableCheck();
        $allProducts = $currentProduct->getTypeInstance()->getUsedProducts($currentProduct, null);

        $helper = $this->helper->getCGVHelper();
        foreach ($allProducts as $product) {
            if ($helper->getConfig('stock_availability') && $helper->getConfig('out_stock')) {
                $products[] = $product;
            } else {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
        }
        return $products;
    }

    /**
     * Get product object based on requested product information
     *
     * @param Product|int|string $productInfo
     * @return Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Product) {
            $product = $productInfo;
            if (!$product->getId()) {
                throw new LocalizedException(__('We can\'t find the product.'));
            }
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->helper->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productInfo, false, $storeId);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('We can\'t find the product.'), $e);
            }
        } else {
            throw new LocalizedException(__('We can\'t find the product.'));
        }
        $currentWebsiteId = $this->helper->getStore()->getWebsiteId();
        if (!is_array($product->getWebsiteIds()) || !in_array($currentWebsiteId, $product->getWebsiteIds())) {
            throw new LocalizedException(__('We can\'t find the product.'));
        }
        return $product;
    }

    /**
     * @param array $ids
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    protected function getProductCollection($ids)
    {
        return $this->productCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => $ids]);
    }
}
