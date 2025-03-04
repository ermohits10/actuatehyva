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
namespace Bss\HidePrice\Plugin\Block\Product\View\Type;

/**
 * Class ConfigurableProductWholesale
 *
 * @package Bss\HidePrice\Plugin\Block\Product\View\Type
 */
class ConfigurableProductWholesale
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Bss\HidePrice\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->helper = $helper;
    }

    /**
     * Add hide price json on Configurable product
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfigTableOrdering($subject, $result)
    {
        $childProduct = $this->helper->getAllData($subject->getProduct()->getEntityId());
        $config = $this->jsonDecoder->decode($result);
        $config["hidePrice"] = $childProduct;
        return $this->jsonEncoder->encode($config);
    }
}
