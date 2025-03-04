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
 * Class DisableCheckout
 *
 * @package Bss\HidePrice\Model\Observer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DisableCheckout implements ObserverInterface
{
    /**
     * @var \Bss\HidePrice\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * DisableCheckout constructor.
     * @param \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice,
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->cartHidePrice = $cartHidePrice;
        $this->helper = $helper;
        $this->cart = $cart;
        $this->redirect = $redirect;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $routeName = $observer->getEvent()->getRequest()->getRouteName();
        $controller = $observer->getControllerAction();
        $disableCheckoutList = $this->cartHidePrice->getDisableCheckout();
        if ($routeName == 'multishipping'
            || in_array($actionName, $disableCheckoutList)
            || $actionName == 'checkout_cart_index'
        ) {
            $message = '';
            $this->checkoutSession->setDisableCheckout(false);
            $disableCheckout = false;
            foreach ($this->cart->getQuote()->getAllVisibleItems() as $item) {
                if ($item->getProductType() == 'configurable') {
                    $parentProductId = $item->getProductId();
                    $childProductSku = $item->getSku();
                    $canShowPrice = $this->cartHidePrice->canShowPrice($parentProductId, $childProductSku, true);
                } else {
                    $canShowPrice = $this->cartHidePrice->canShowPrice($item->getProductId(), false, true);
                }
                if (!$canShowPrice) {
                    $disableCheckout = true;
                    if ($actionName == 'checkout_cart_index') {
                        if ($message != '') {
                            $message .= ', ';
                        }
                        $message .= $item->getName();
                    } else {
                        break;
                    }
                }
            }
            if ($disableCheckout) {
                $this->checkoutSession->setDisableCheckout(true);
                if ($actionName != 'checkout_cart_index') {
                    $this->redirect->redirect($controller->getResponse(), 'checkout/cart/index');
                } else {
                    if ($message != '') {
                        $defaultHidePriceMessage = $this->helper->getHidePriceTextGlobal();
                        if ($defaultHidePriceMessage != '') {
                            $defaultHidePriceMessage = ' ' . $defaultHidePriceMessage;
                        }
                        $this->messageManager->addErrorMessage(__("%1 can't checkout now.", $message) . $defaultHidePriceMessage);
                    }
                    return $this;
                }

            } else {
                return $this;
            }
        } else {
            return $this;
        }
    }
}
