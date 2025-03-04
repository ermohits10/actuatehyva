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
namespace Bss\HidePrice\Plugin\Paypal\Model;

/**
 * Class AbstractConfig
 *
 * @package Bss\HidePrice\Plugin\Paypal\Model
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AbstractConfig
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
     * @param \Magento\Paypal\Model\AbstractConfig $subject
     * @param \Closure $proceed
     * @param string $key
     * @param null|int $storeId
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetValue(
        \Magento\Paypal\Model\AbstractConfig $subject,
        \Closure $proceed,
        $key,
        $storeId = null
    ) {
        if ($key == 'visible_on_cart') {
            if ($this->checkoutSession->getDisableCheckout()) {
                return false;
            }
        }
        return $proceed($key, $storeId);
    }
}
