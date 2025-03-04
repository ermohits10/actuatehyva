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
namespace Bss\HidePrice\Plugin\Block\Ui;

use Bss\HidePrice\Helper\Data;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ProductViewCounter
 *
 * @package Bss\HidePrice\Plugin\Block\Ui
 */
class ProductViewCounter
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * ProductViewCounter constructor.
     * @param Data $helper
     * @param SerializerInterface $serialize
     * @param Registry $registry
     * @param PostHelper $postHelper
     */
    public function __construct(
        Data $helper,
        SerializerInterface $serialize,
        Registry $registry,
        PostHelper $postHelper
    ) {
        $this->helper = $helper;
        $this->serialize = $serialize;
        $this->registry = $registry;
        $this->postHelper = $postHelper;
    }

    /**
     * Set hide price to current product data
     *
     * @param \Magento\Catalog\Block\Ui\ProductViewCounter $subject
     * @param string $result
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCurrentProductData(
        $subject,
        $result
    ) {
        $product = $this->registry->registry('product');
        if ($product && $productId = $product->getId()) {
            if ($this->helper->activeHidePrice($product)
                || ($product->getTypeId() == 'grouped'
                    && $this->helper->activeHidePriceGrouped($product))
            ) {
                $currentProductData = $this->serialize->unserialize($result);
                $message = $this->helper->getHidePriceText($product);
                $url = $this->helper->getHidePriceUrl($product);
                $postData = $this->postHelper->getPostData(
                    $url,
                    ['product' => $product->getId(),
                        \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => "%uenc%"
                    ]
                );
                if ($this->helper->hidePriceActionActive($product) != 2) {
                    unset($currentProductData['items'][$productId]['price_info']);
                }
                $currentProductData['items'][$productId]['add_to_cart_button']['hide_price'] = $message;
                $currentProductData['items'][$productId]['add_to_cart_button']['hide_price_url'] = $url;
                $currentProductData['items'][$productId]['add_to_cart_button']['hide_price_post'] = $postData;
                return $this->serialize->serialize($currentProductData);
            }
        }
        return $result;
    }
}
