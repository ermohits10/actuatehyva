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

namespace Bss\HidePrice\Plugin\Block\Product;

class View
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serialize;

    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Bss\HidePrice\Helper\Data $helper
    )
    {
        $this->serialize = $serialize;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfig($subject, $result)
    {
        if ($this->helper->isEnable()) {
            $product = $subject->getProduct();
            if ($this->helper->activeHidePrice($product) && $this->helper->hidePriceActionActive($product) != 2) {
                $config = $this->serialize->unserialize($result);
                $config['prices']['oldPrice']['amount'] = 0;
                $config['prices']['basePrice']['amount'] = 0;
                $config['prices']['finalPrice']['amount'] = 0;
                $config['tierPrices'] = [];
                return $this->serialize->serialize($config);
            }
        }
        return $result;
    }
}
