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
namespace Bss\HidePrice\Plugin;

use Magento\Catalog\Pricing\Price\TierPrice as TierPrice;

/**
 * Class HideTierPrice
 *
 * @package Bss\HidePrice\Plugin
 */
class HideTierPrice
{
    /**
     * Data
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * HideTierPrice constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Hide Add to cart Button
     *
     * @param TierPrice $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetTierPriceList(
        TierPrice $subject,
        $result
    ) {
        $product = $subject->getProduct();

        if ($this->helper->activeHidePrice($product)) {
            $result = [];
        }
        return $result;
    }
}
