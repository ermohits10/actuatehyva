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
namespace Bss\HidePrice\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class PrepareProductPrice
 *
 * @package Bss\HidePrice\Model\Observer
 */
class PrepareProductPrice implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * ApplyHideOnProductAfterLoadObserver constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Set hide price message compile with CPWD + Fastorder
     *
     * @param EventObserver $observer
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getProduct();
        $product->setBssHidePriceHtml('');
        if ($this->helper->activeHidePrice($product) && $this->helper->hidePriceActionActive($product) == 1) {
            $product->setBssHidePrice(true);
            $product->setBssDisableCart(true);
            $mes = $this->helper->getHidepriceMessage($product, false);
            $product->setBssHidePriceHtml($mes);
        }
        if ($this->helper->activeHidePrice($product) && $this->helper->hidePriceActionActive($product) == 2) {
            $product->setBssHidePriceHtml(__('You can not add these products to cart.'));
        }
    }
}
