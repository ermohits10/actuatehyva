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
namespace Bss\HidePrice\Plugin\Checkout\Block\Cart;

/**
 * Class Shipping
 *
 * @package Bss\HidePrice\Plugin\Checkout\Block\Cart
 */
class Shipping
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
     * @param \Magento\Checkout\Block\Cart\Shipping $subject
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJsLayout(
        \Magento\Checkout\Block\Cart\Shipping $subject,
        $result
    ) {
        if ($this->checkoutSession->getDisableCheckout()) {
            $result = json_decode($result, JSON_HEX_TAG);
            unset($result['components']['summary-block-config']);
            unset($result['components']['block-summary']);
            $result = json_encode($result, JSON_HEX_TAG);
        }
        return $result;
    }
}
