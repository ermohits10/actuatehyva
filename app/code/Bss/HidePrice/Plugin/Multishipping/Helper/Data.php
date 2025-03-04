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
namespace Bss\HidePrice\Plugin\Multishipping\Helper;

/**
 * Class Data
 *
 * @package Bss\HidePrice\Plugin\Multishipping\Helper
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * AbstractConfig constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Multishipping\Helper\Data $subject
     * @param $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsMultishippingCheckoutAvailable(
        \Magento\Multishipping\Helper\Data $subject,
        $result
    ) {
        if ($this->checkoutSession->getDisableCheckout()) {
            return false;
        }
        return $result;
    }
}
