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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Api\Helper;

use Bss\HidePrice\Helper\Api as helperApi;

/**
 * Class Data
 */
class Data
{
    /**
     * @var \Bss\HidePrice\Helper\Api
     */
    protected $helperApi;

    /**
     * QueryProducts constructor.
     *
     * @param helperApi $helperApi
     */
    public function __construct(
        helperApi $helperApi
    ) {
        $this->helperApi = $helperApi;
    }

    /**
     * Set customer group id when use graphql
     *
     * @param \Bss\HidePrice\Helper\Data $subject
     * @param int $result
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomerGroupId($subject, int $result)
    {
        $customerId = $this->helperApi->getCustomerId();
        if ($customerId) {
            return $this->helperApi->getCustomerGroupId($customerId);
        }
        return $result;
    }

}
