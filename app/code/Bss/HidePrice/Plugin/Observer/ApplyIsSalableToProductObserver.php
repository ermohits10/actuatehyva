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
namespace Bss\HidePrice\Plugin\Observer;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class ApplyIsSalableToProductObserver
 *
 * @package Bss\HidePrice\Plugin\Observer
 */
class ApplyIsSalableToProductObserver
{
    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * ApplyIsSalableToProductObserver constructor.
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Saleable product
     *
     * @param \Magento\CatalogPermissions\Observer\ApplyIsSalableToProductObserver $subject
     * @param mixed $result
     * @param EventObserver $observer
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result, EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($this->helper->activeHidePrice($product) && $product->getData('is_salable') == 1) {
            $observer->getEvent()->getSalable()->setIsSalable(true);
        }
        return $result;
    }
}
