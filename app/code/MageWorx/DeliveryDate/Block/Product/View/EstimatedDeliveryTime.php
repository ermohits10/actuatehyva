<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MageWorx\DeliveryDate\Helper\Data as Helper;

/**
 * Class EstimatedDeliveryTime
 *
 * Block designed to display estimated delivery time on the product view page
 *
 */
class EstimatedDeliveryTime extends Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Resolver
     */
    protected $localeResolver;

    /**
     * @var SerializerJson
     */
    private $serializer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * EstimatedDeliveryTime constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Helper $helper
     * @param SerializerJson $serializer
     * @param ProductRepositoryInterface $productRepository
     * @param TimezoneInterface $timezone
     * @param Resolver $localeResolver
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        Helper                     $helper,
        SerializerJson             $serializer,
        ProductRepositoryInterface $productRepository,
        TimezoneInterface          $timezone,
        Resolver                   $localeResolver,
        array                      $data = []
    ) {
        $this->_coreRegistry     = $registry;
        $this->helper            = $helper;
        $this->serializer        = $serializer;
        $this->productRepository = $productRepository;
        $this->timezone          = $timezone;
        $this->localeResolver    = $localeResolver;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getJsLayout(): string
    {
        $this->processJsLayout();

        return $this->serializer->serialize($this->jsLayout);
    }

    /**
     * Add js layout for configurable products DD component
     */
    private function processJsLayout(): void
    {
        $this->jsLayout = $this->jsLayout ?? [];
        $product        = $this->getProduct();

        $this->jsLayout['components']['delivery-date-info']['selectOptionsMessageEnabled'] =
            $this->helper->isSelectProductOptionsMessageVisible();
        $this->jsLayout['components']['delivery-date-info']['selectOptionsMessage']        =
            $this->helper->getSelectProductOptionsMessage();
        $this->jsLayout['components']['delivery-date-info']['errorMessage']                =
            $this->getErrorMessage();
        $this->jsLayout['components']['delivery-date-info']['displayErrorMessage']         =
            $this->isAllowedByProduct($product) && $this->isDisplayErrorMessage();
        $this->jsLayout['components']['delivery-date-info']['deliveryDateMessage']         =
            $this->helper->getProductEDTMessageFormat();
        $this->jsLayout['components']['delivery-date-info']['timezone']                    =
            $this->timezone->getConfigTimezone();
        $this->jsLayout['components']['delivery-date-info']['locale']                      =
            $this->getLocaleForJs();

        $this->jsLayout['components']['delivery-date-info']['mainProduct'] = [
            'from' => (int)$product->getData('mw_delivery_time_from'),
            'to'   => (int)$product->getData('mw_delivery_time_to'),
        ];

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $this->jsLayout['components']['delivery-date-info']['cpid'] = $product->getId();

            try {
                $childItems = $this->getProductChildItemsConfig($product);
            } catch (NoSuchEntityException $e) {
                $childItems = [];
            }
            $this->jsLayout['components']['delivery-date-info']['childItems'] = $childItems;
        }
    }

    /**
     * @param Product $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductChildItemsConfig(\Magento\Catalog\Model\Product $product): array
    {
        $productTypeInstance = $product->getTypeInstance();
        $usedProducts        = $productTypeInstance->getUsedProducts($product);
        $config              = [];

        $attributes         = $productTypeInstance->getConfigurableAttributes($product);
        $superAttributeList = [];
        foreach ($attributes as $_attribute) {
            $attributeCode                                     = $_attribute->getProductAttribute()->getAttributeCode();
            $superAttributeList[$_attribute->getAttributeId()] = $attributeCode;
        }

        foreach ($usedProducts as $usedProduct) {
            $usedProduct = $this->productRepository->get(
                $usedProduct->getSku(),
                false,
                $product->getStoreId(),
                true
            );
            $id          = $usedProduct->getId();
            $config[$id] = [
                'super_attributes'      => [],
                'delivery_date_visible' => false,
                'from'                  => (int)$usedProduct->getData('mw_delivery_time_from'),
                'to'                    => (int)$usedProduct->getData('mw_delivery_time_to'),
            ];

            foreach ($superAttributeList as $superAttrId => $superAttribute) {
                $config[$id]['super_attributes'][$superAttrId] = $usedProduct->getData($superAttribute);
            }

            $isVisible = $this->isVisibleOnProduct($usedProduct);
            $isAllowed = $this->isAllowedByProduct($usedProduct);

            if (!$isVisible) {
                $config[$id]['delivery_date_visible'] = false;
                continue;
            }

            if (!$isAllowed) {
                $config[$id]['delivery_date_visible'] = $this->isDisplayErrorMessage();
                $config[$id]['error'] = true;
                $config[$id]['delivery_date_error_message'] = $this->getErrorMessage();
                continue;
            }

            $config[$id]['delivery_date_visible'] = true;
        }

        return $config;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }

        return $this->_product;
    }

    /**
     * Is display of Estimated Delivery Time enabled on the product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|null $product
     * @return bool
     */
    public function isVisibleOnProduct(\Magento\Catalog\Api\Data\ProductInterface $product = null)
    {
        $product = $product ?? $this->getProduct();

        $visible = (bool)$product->getData('mw_delivery_time_visible');
        if (!$visible) {
            return false;
        }

        return $visible;
    }

    /**
     * Is Estimated Delivery Time enabled for the product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|null $product
     * @return bool
     */
    public function isAllowedByProduct(\Magento\Catalog\Api\Data\ProductInterface $product = null)
    {
        $product = $product ?? $this->getProduct();

        $allowed = (bool)$product->getData('mw_delivery_time_enabled');
        if (!$allowed) {
            return false;
        }

        return $allowed;
    }

    /**
     * Is error message should be visible on front
     *
     * @return bool
     */
    public function isDisplayErrorMessage(): bool
    {
        return $this->helper->isErrorMessageVisibleOnProduct();
    }

    /**
     * Error message in case when Delivery Date unavailable right now
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->helper->getDeliveryDateProductErrorMessage();
    }

    /**
     * Convert PHP locale to JS locale supported by Intl
     *
     * @return string
     */
    public function getLocaleForJs(): string
    {
        return str_ireplace(
            '_',
            '-',
            $this->localeResolver->getLocale()
        );
    }
}
