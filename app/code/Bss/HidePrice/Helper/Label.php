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

namespace Bss\HidePrice\Helper;

use Bss\HidePrice\Model\Config\Source\HidePriceActionSystem;
use Bss\HidePrice\Model\Config\Source\HidePriceCategories;
use Bss\HidePrice\Model\Config\Source\ProductHidePriceCustomerGroupSystem;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * @package Bss\HidePrice\Helper
 */
class Label extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ProductHidePriceCustomerGroupSystem
     */
    protected $customerGroupSystem;

    /**
     * @var HidePriceCategories
     */
    protected $categories;

    /**
     * @var HidePriceActionSystem
     */
    protected $actionSystem;

    /**
     * Data constructor.
     * @param ProductHidePriceCustomerGroupSystem $customerGroupSystem
     * @param HidePriceCategories $categories
     * @param HidePriceActionSystem $actionSystem
     * @param Context $context
     */
    public function __construct(
        ProductHidePriceCustomerGroupSystem $customerGroupSystem,
        HidePriceCategories $categories,
        HidePriceActionSystem $actionSystem,
        Context $context
    ) {
        $this->customerGroupSystem = $customerGroupSystem;
        $this->categories = $categories;
        $this->actionSystem = $actionSystem;
        parent::__construct($context);
    }

    /**
     * Get label action
     *
     * @param int $codeAction
     * @return string|null
     */
    public function getLabelAction($codeAction)
    {
        $allOptions = $this->actionSystem->getAllOptions();
        if (isset($allOptions[$codeAction])) {
            return $allOptions[$codeAction];
        }
        return null;
    }

    /**
     * @param string $codeCustomers
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLabelCustomers($codeCustomers)
    {
        $data = "";
        if ($codeCustomers) {
            $customers = $this->customerGroupSystem->getAllOptions();
            $codeCustomers = explode(",", $codeCustomers);
            $data = $this->getAllLabel($customers, $codeCustomers);
        }
        return $data;
    }
    /**
     * Get label categories by code categories
     *
     * @param string $codeCategories
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLabelCategories($codeCategories)
    {
        $data = "";
        if ($codeCategories) {
            $categories = $this->categories->toOptionArray();
            $codeCategories = explode(",", $codeCategories);
            $data = $this->getAllLabel($categories, $codeCategories);
        }
        return $data;
    }

    /**
     * Get all label by codes
     *
     * @param array $allOptions
     * @param array $codes
     * @return string
     */
    public function getAllLabel($allOptions, $codes)
    {
        $data = "";
        $i = 0;
        foreach ($codes as $code) {
            if ($i === 0) {
                $data = $this->getLabel($allOptions, $code);
            } else {
                $data .= "," . $this->getLabel($allOptions, $code);
            }
            $i ++;
        }
        return $data;
    }

    /**
     * Get label category by code category
     *
     * @param array $allOptions
     * @param int $code
     * @return string
     */
    public function getLabel($allOptions, $code)
    {
        if (isset($allOptions[$code])) {
            return $allOptions[$code]["label"];
        }
        return "";
    }
}
