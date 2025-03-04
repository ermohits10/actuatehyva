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
namespace Bss\HidePrice\Plugin\Catalog\Block\Product\View\Options;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AbstractOptions
 *
 * @package Bss\HidePrice\Plugin\Catalog\Block\Product\View\Options
 */
class AbstractOptions
{
    /**
     * Hide option price html
     *
     * @param \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject
     * @param string $result
     * @return string
     * @throws NoSuchEntityException
     */
    public function afterGetFormattedPrice(
        $subject,
        $result
    ) {
        if ($subject->getProduct()->getCanShowPrice() === false
            || $subject->getProduct()->getCanShowBundlePrice() === false
        ) {
            return '';
        }
        return $result;
    }

    /**
     * After Get Values Html
     *
     * @param \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValuesHtml($subject, $result)
    {
        if ($subject->getProduct()->getCanShowPrice() === false
            || $subject->getProduct()->getCanShowBundlePrice() === false
        ) {
            $result = str_replace('<span class="price-notice">+</span>', '', $result);
        }
        return $result;
    }
}
