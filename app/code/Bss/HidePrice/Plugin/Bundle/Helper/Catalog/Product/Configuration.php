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
namespace Bss\HidePrice\Plugin\Bundle\Helper\Catalog\Product;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Class Configuration
 *
 * @package Bss\HidePrice\Plugin\Bundle\Helper\Catalog\Product\Configuration
 */
class Configuration
{
    /**
     * Data
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * HideButtonCart constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Hide option price
     *
     * @param \Magento\Bundle\Helper\Catalog\Product\Configuration $subject
     * @param string $result
     * @param ItemInterface $item
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelectionFinalPrice(
        $subject,
        $result,
        ItemInterface $item,
        \Magento\Catalog\Model\Product $selectionProduct
    ) {
        if ($selectionProduct->getCanShowPrice() === false) {
            $dataMessage = $this->helper->getHidepriceMessageLink($selectionProduct);
            if (is_array($dataMessage)) {
                $dataMessage = __(
                    '<a href="%1">' . $dataMessage["message"] . '</a>',
                    $dataMessage['link']
                );
            }
            return 'BssHidePrice(' . $dataMessage . ')';
        }
        return $result;
    }
}
