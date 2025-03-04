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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Pricing\Helper;

/**
 * Class Data
 *
 * @package Bss\HidePrice\Plugin\Pricing\Helper
 */
class Data
{
    /**
     * @param \Magento\Framework\Pricing\Helper\Data $subject
     * @param \Closure $proceed
     * @param mixed $value
     * @param bool $format
     * @param bool $includeContainer
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCurrency(
        \Magento\Framework\Pricing\Helper\Data $subject,
        \Closure $proceed,
        $value,
        $format = true,
        $includeContainer = true
    ) {
        if (is_string($value) && strpos($value, 'BssHidePrice') !== false) {
            return str_replace("BssHidePrice", "", $value);
        }
        return $proceed($value, $format, $includeContainer);
    }
}
