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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Api;

use Bss\HidePrice\Helper\Api as HelperApi;

/**
 * Class CategoryHidePrice
 *
 * @package Bss\HidePrice\Plugin
 */
class QueryProducts
{
    /**
     * @var null|int
     */
    protected $customerGroupId = null;

    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $HelperApi;

    /**
     * QueryProducts constructor.
     *
     * @param HelperApi $HelperApi
     */
    public function __construct(
        HelperApi $HelperApi
    ) {
        $this->HelperApi = $HelperApi;
    }

    /**
     * Add extension attribute "disable_add_to_cart" and "can_show_price"
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
    ) {
        $extensionAttributes = $entity->getExtensionAttributes(); /** get current extension attributes from entity **/
        $extensionAttributes->setDisableAddToCart($entity->getDisableAddToCart());
        $extensionAttributes->setCanShowPrice($entity->getCanShowPrice());
        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

    /**
     * Add extension attribute "disable_add_to_cart" and "can_show_price"
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ) : \Magento\Catalog\Api\Data\ProductSearchResultsInterface {
        $products = [];
        foreach ($searchCriteria->getItems() as $entity) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setDisableAddToCart($entity->getDisableAddToCart());
            $extensionAttributes->setCanShowPrice($entity->getCanShowPrice());
            $entity->setExtensionAttributes($extensionAttributes);
            $products[] = $entity;
        }
        $searchCriteria->setItems($products);
        return $searchCriteria;
    }

}
