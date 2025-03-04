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
namespace Bss\HidePrice\Block;

use Bss\HidePrice\Helper\Data as Helper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class HideButton
 *
 * @package Bss\HidePrice\Block
 */
class HideButton extends Template
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * HideButton constructor.
     *
     * @param Template\Context $context
     * @param Helper $helper
     * @param ProductMetadataInterface $productMetaData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Helper $helper,
        ProductMetadataInterface $productMetaData,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->productMetaData = $productMetaData;
        parent::__construct($context, $data);
    }

    /**
     * Get bss helper
     *
     * @return Helper
     */
    public function returnHelper()
    {
        return $this->helper;
    }

    /**
     * Compare version 2.3.1.
     *
     * Magento 2.3.1 Add Pagination in Wish list account page.
     *
     * @return bool
     */
    public function compareVersion()
    {
        $version = $this->productMetaData->getVersion();
        return version_compare($version, '2.3.1', '<');
    }
}
