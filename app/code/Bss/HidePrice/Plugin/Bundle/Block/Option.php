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
namespace Bss\HidePrice\Plugin\Bundle\Block;

use Magento\Catalog\Model\Product;

/**
 * Class Option
 *
 * @package Bss\HidePrice\Plugin\Bundle\Block
 */
class Option
{
    /**
     * Helper
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * Option constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param \Closure $proceed
     * @param $selection
     * @param bool $includeContainer
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundRenderPriceString(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        \Closure $proceed,
        $selection,
        $includeContainer = true
    ) {
        $parentShowPrice = $this->helper->hidePriceActionActive($subject->getProduct());
        if ($this->helper->activeHidePrice($subject->getProduct())
            && $parentShowPrice != 2
            && $subject->getProduct()->getTypeId() == 'bundle'
        ) {
            return ' ' . $this->helper->getHidepriceMessage($selection);
        } elseif ($this->helper->activeHidePrice($selection) && $parentShowPrice != 2) {
            if ($this->helper->hidePriceActionActive($selection) == 2) {
                $result = $proceed($selection, $includeContainer);
                return $result . $this->helper->getHidepriceMessage($selection);
            } else if ($this->helper->hidePriceActionActive($selection) == 1) {
                return ' ' . $this->helper->getHidepriceMessage($selection);
            }
            
        }

        return $proceed($selection, $includeContainer);
    }

    /**
     * Remove '+' title option when product hide
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param string $result
     * @param object $selection
     * @param bool $includeContainer
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelectionTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result,
        $selection,
        $includeContainer = true
    ) {
        if ($this->helper->activeHidePrice($subject->getProduct())
            && $this->helper->hidePriceActionActive($subject->getProduct()) != 2
            && $subject->getProduct()->getTypeId() == 'bundle'
        ) {
            $result = str_replace(
                '<span class="price-notice">+</span>',
                '',
                $result
            );
        } elseif ($this->helper->activeHidePrice($selection) && $this->helper->hidePriceActionActive($selection) != 2) {
            $result = str_replace(
                '<span class="price-notice">+</span>',
                '',
                $result
            );
        }
        return $result;
    }

    /**
     * Remove '+' title option when product hide
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param mixed $result
     * @param Product $selection
     * @param bool $includeContainer
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelectionQtyTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result,
        $selection,
        $includeContainer = true
    ) {
        if ($this->helper->activeHidePrice($subject->getProduct())
            && $this->helper->hidePriceActionActive($subject->getProduct()) != 2
            && $subject->getProduct()->getTypeId() == 'bundle'
        ) {
            $result = str_replace(
                '<span class="price-notice">+</span>',
                '',
                $result
            );
        } elseif ($this->helper->activeHidePrice($selection) && $this->helper->hidePriceActionActive($selection) != 2) {
            $result = str_replace(
                '<span class="price-notice">+</span>',
                '',
                $result
            );
        }
        return $result;
    }

    /**
     * Hide option price html
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param string $result
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderPriceString(
        $subject,
        $result,
        $selection,
        $includeContainer = true
    ) {
        $product = $subject->getProduct();
        if ($this->helper->activeHidePrice($product)
            && $this->helper->hidePriceActionActive($product) != 2
        ) {
            return '';
        }
        return $result;
    }
}
