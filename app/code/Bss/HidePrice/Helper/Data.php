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

namespace Bss\HidePrice\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\HidePrice\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'bss_hide_price/general/enable';
    const XML_PATH_SELECTOR = 'bss_hide_price/general/selector';
    const XML_HIDE_PRICE_DISABLE_CHECKOUT_CONTROLLER = 'bss_hide_price/general/disable_checkout';
    const XML_PATH_HIDE_PRICE_ACTION = 'bss_hide_price/hideprice_global/action';
    const XML_HIDE_PRICE_CATEGORIES = 'bss_hide_price/hideprice_global/categories';
    const XML_HIDE_PRICE_CUSTOMERS = 'bss_hide_price/hideprice_global/customers';
    const XML_PATH_HIDE_PRICE_TEXT = 'bss_hide_price/hideprice_global/text';
    const XML_PATH_HIDE_PRICE_URL = 'bss_hide_price/hideprice_global/hide_price_url';

    /**
     * Options HidePrice attribute
     */
    const USER_GLOBAL_CONFIG = 0;
    const DISABLE = -1;
    const HIDE_PRICE_ADD_2_CART = 1;
    const SHOW_PRICE_HIDE_ADD_2_CART = 2;

    protected $customerGroupId;

    /**
     * ScopeConfig
     *
     * @var $scopeConfig
     */
    protected $scopeConfig;

    /**
     * ProductRepository
     *
     * @var $productRepository
     */
    protected $productRepository;

    /**
     * StoreManagerInterface
     *
     * @var $storeManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * Url Builder
     *
     * @var $urlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Customer\Model\Session|\Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * Configurable
     *
     * @var $configurableData
     */
    protected $configurableData;

    /**
     * Registry
     *
     * @var $registry
     */
    protected $registry;

    /**
     * @var null
     */
    private $store = null;

    /**
     * @var ConfigurableGridViewHelper
     */
    protected $cgvHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $pr
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param ConfigurableGridViewHelper $cgvHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Customer\Model\SessionFactory $customerSession,
        ConfigurableGridViewHelper $cgvHelper
    ) {
        $this->productRepository = $pr;
        parent::__construct($context);
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->storeManagerInterface = $storeManagerInterface;
        $this->configurableData = $configurableData;
        $this->customerSession = $customerSession;
        $this->cgvHelper = $cgvHelper;
    }

    /**
     * Get Configurable grid view helper
     *
     * @return ConfigurableGridViewHelper
     */
    public function getCGVHelper()
    {
        return $this->cgvHelper;
    }

    /**
     * Get store
     *
     * @return \Magento\Store\Api\Data\StoreInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        if (!$this->store) {
            $this->store = $this->storeManagerInterface->getStore();
        }
        return $this->store;
    }

    /**
     * Is enable module
     *
     * @param int|null $store
     * @return mixed
     */
    public function isEnable($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Selector
     *
     * @param int $store
     * @return string
     */
    public function getSelector($store = null)
    {
        $selector = $this->scopeConfig->getValue(
            self::XML_PATH_SELECTOR,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($selector == '') {
            $selector = '.action.tocart';
        }
        return $selector;
    }

    /**
     * Retrieve HidePrice Action
     *
     * @param int $store
     * @return string
     */
    public function getHidePriceAction($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HIDE_PRICE_ACTION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve HidePrice Categories
     *
     * @param int $store
     * @return string
     */
    public function getHidePriceCategories($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_HIDE_PRICE_CATEGORIES,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve HidePrice Customers
     *
     * @param int $store
     * @return string
     */
    public function getHidePriceCustomers($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_HIDE_PRICE_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get hide price url
     *
     * @param int|null $storeId
     * @return string
     */
    public function getHidePriceUrlConfig($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HIDE_PRICE_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }



    /**
     * @param null $store
     * @return mixed
     */
    public function getDisableCheckout($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_HIDE_PRICE_DISABLE_CHECKOUT_CONTROLLER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Item Product
     *
     * @param int $itemId
     * @return object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemProduct($itemId)
    {
        return $this->productRepository->getById($itemId, false);
    }

    /**
     * Call Product
     *
     * @return object
     */
    public function callProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * Get Customer GroupId
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->customerGroupId) {
            return $this->customerGroupId;
        }
        $customer = $this->customerSession->create();
        if ($customer->getId()) {
            return $customer->getCustomer()->getGroupId();
        }
        return 0;
    }

    /**
     * Get Hide Price Message
     *
     * @param object $product
     * @param bool $includeUrl
     * @return string
     */
    public function getHidepriceMessage($product, $includeUrl = true)
    {
        $message = $this->getHidePriceText($product);

        if ($this->getHidePriceUrl($product) && $includeUrl) { //product have hide price url
            return '<a href="' . trim($this->getHidePriceUrl($product)) . '">' . $message . '</a>';
        } else {
            return $message;
        }
    }

    /**
     * Get hide price link
     *
     * @param Product $product
     * @param bool $includeUrl
     * @return array|string
     */
    public function getHidepriceMessageLink($product, $includeUrl = true)
    {
        $message = $this->getHidePriceText($product);

        if ($this->getHidePriceUrl($product) && $includeUrl) { //product have hide price url
            return ['link' => trim($this->getHidePriceUrl($product)), 'message' => $message];
        } else {
            return $message;
        }
    }

    /**
     * Get Hide Price Action Product
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceActionProduct($product)
    {
        return $product->getHidepriceAction();
    }

    /**
     * Get Hide Text
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceText($product)
    {
        if ($product->getHidepriceMessage() && $product->getHidepriceAction() > 0) {
            return $product->getHidepriceMessage();
        } else {
            $_message = $this->scopeConfig->getValue(
                self::XML_PATH_HIDE_PRICE_TEXT,
                ScopeInterface::SCOPE_STORE
            );
            if ($_message) {
                return $_message;
            } else {
                return __('Please contact us for price.');
            }
        }
    }

    /**
     * Get hide price text global
     *
     * @param int|null $storeId
     * @return string
     */
    public function getHidePriceTextGlobal($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HIDE_PRICE_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Hide Url
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceUrl($product)
    {
        if (($product->getHidepriceAction() == 0
            || !$product->getHidepriceAction())) {
            $hidePriceUrl = $this->scopeConfig->getValue(
                self::XML_PATH_HIDE_PRICE_URL,
                ScopeInterface::SCOPE_STORE
            );
            if (!trim($hidePriceUrl)) {
                return false;
            }

            return trim($hidePriceUrl);
        }
        return trim($product->getHidepriceUrl());
    }

    /**
     * Filter Array
     *
     * @param string $string
     * @return array
     */
    public function filterArray($string)
    {
        $array = explode(',', $string);
        $newArray = array_filter($array, function ($value) {
            return $value !== '';
        });
        return $newArray;
    }

    /**
     * Active Hide Price
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param mixed $storeId
     * @param bool $isChild
     * @param bool $cusGroupId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function activeHidePrice($product, $storeId = null, $isChild = false, $cusGroupId = false)
    {
        if ($this->isEnable($storeId)) {
            if ($isChild) {
                $product = $this->productRepository->getById($product->getId());
            }
            if ($product->getHidepriceAction() == -1) { // product disabled
                return false;
            } elseif ($product->getHidepriceAction() == 0 || $product->getHidepriceAction() == '') { // global config
                if ($cusGroupId) {
                    $this->customerGroupId = $cusGroupId;
                }
                return $this->hidePriceCustomersGroupGlobal($product);
            } else { // product config
                if ($cusGroupId) {
                    $this->customerGroupId = $cusGroupId;
                }
                if (!$this->hidePriceCustomersGroupProduct($product)) { //product not set customer group
                    return false;
                } else { // check product setting
                    if ($this->hidePriceCustomersGroupProduct($product)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } else { // disabled
            return false;
        }
    }

    /**
     * Active HidePrice Grouped Product
     *
     * @param object $product
     * @param int $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function activeHidePriceGrouped($product, $storeId = null)
    {
        if ($product->getTypeId() != "grouped") {
            return true;
        }
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        $hasAssociatedProducts = count($associatedProducts) > 0;
        if ($hasAssociatedProducts) {
            foreach ($associatedProducts as $item) {
                $childProduct = $this->productRepository->getById($item->getId());
                if (!$this->activeHidePrice($childProduct, $storeId)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Hide Price Action Active
     *
     * @param object $product
     * @return int|string
     */
    public function hidePriceActionActive($product)
    {
        if ($this->isEnable()) {
            if ($product->getHidepriceAction() == -1) {
                return 0;
            } elseif ($product->getHidepriceAction() == 0 || $product->getHidepriceAction() == '') {
                return $this->getHidePriceAction();
            } else {
                return $product->getHidepriceAction();
            }
        } else {
            return 0;
        }
    }

    /**
     * Get all data from product
     *
     * @param int $productEntityId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllData($productEntityId)
    {
        $result = [];
        $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
        $product = $this->productRepository->getById($productEntityId);
        if ($this->activeHidePrice($product)) {
            if ($this->hidePriceActionActive($product) == 1) {
                $result['hide_price_parent'] = true;
                $result['hide_price_parent_content'] = $this->getHidepriceMessage($product);
            } elseif ($this->hidePriceActionActive($product) == 2) {
                $result['hide_price_parent'] = false;
            }
            return $result;
        }

        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        $result['entity'] = $productEntityId;

        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = [];
            $childProduct['entity'] = $simpleProduct;
            $child = $this->productRepository->getById($childProduct['entity']);
            $childProduct['hide_price'] = $this->activeHidePrice($child);
            if ($childProduct['hide_price']) {
                $childProduct['hide_price_content'] = '<p id="hide_price_text_' . $child->getId()
                    . '" class="hide_price_text">' . $this->getHidepriceMessage($child) . '</p>';
                $childProduct['show_price'] = $this->hidePriceActionActive($child) == 2;
            } else {
                $childProduct['hide_price_content'] = false;
                $childProduct['show_price'] = false;
            }
            $key = '';
            foreach ($parentAttribute as $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                $key .= $child->getData($attrLabel) . '_';
            }
            $result['child'][$key] = $childProduct;
        }
        $result['parent_id'] = $product->getId();
        $result['selector'] = $this->getSelector();
        return $result;
    }

    /**
     * Check hide price for customer group
     *
     * @param Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    private function hidePriceCustomersGroupProduct($product)
    {
        $hidePriceCustomersGroupProduct = $this->filterArray($product->getHidepriceCustomergroup());
        $customerGroup = $this->getCustomerGroupId();
        if (!empty($hidePriceCustomersGroupProduct)
            && count($hidePriceCustomersGroupProduct) == 1
            && $hidePriceCustomersGroupProduct[0] == -1) {
            return false;
        }

        if (!empty($hidePriceCustomersGroupProduct)
            && in_array($customerGroup, $hidePriceCustomersGroupProduct)) {
            return true;
        }
        return false;
    }

    /**
     * Is enable hide price global config
     *
     * @param object $product
     * @return bool
     */
    private function hidePriceCustomersGroupGlobal($product)
    {
        $hidePriceCategories = $this->filterArray($this->getHidePriceCategories());
        $hidePriceCustomers = $this->filterArray($this->getHidePriceCustomers());

        $productCategories = $product->getCategoryIds() ? array_filter($product->getCategoryIds()) : [];
        $customerGroup = $this->getCustomerGroupId();
        if (empty($hidePriceCustomers)
            || !in_array($customerGroup, $hidePriceCustomers)
            || empty(array_intersect($productCategories, $hidePriceCategories))
        ) {
            return false;
        }
        return true;
    }
}
