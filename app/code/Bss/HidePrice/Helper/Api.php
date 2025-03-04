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
namespace Bss\HidePrice\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * @package Bss\HidePrice\Helper
 */
class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var int
     */
    public $customerGroupId;

    /**
     * Data constructor.
     *
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Context $context
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        Context $context
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * Get customer group id by id customer
     *
     * @param int|null $customerId
     * @return int|null
     */
    public function getCustomerGroupId($customerId =null)
    {
        if (!$this->customerGroupId) {
            try {
                $this->customerGroupId = 0;
                if ($customerId) {
                    $customer = $this->customerRepositoryInterface->getById($customerId);
                    $this->customerGroupId = (int) $customer->getGroupId();
                }
                return $this->customerGroupId;
            } catch (\Exception $exception) {
                $this->_logger->critical($exception->getMessage());
                return null;
            }
        }
        return $this->customerGroupId;
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_getRequest()->getParam("customerId");
    }

}
