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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Bss\HidePrice\Helper\Api as HelperApi;

/**
 * Class CartManagement
 */
class CartManagement
{
    /**
     * @var HelperApi
     */
    protected $helperApi;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Bss\HidePrice\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * Data constructor.
     * @param \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
     */
    public function __construct(
        HelperApi $helperApi,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\HidePrice\Helper\CartHidePrice $cartHidePrice
    ) {
        $this->helperApi = $helperApi;
        $this->quoteRepository = $quoteRepository;
        $this->cartHidePrice = $cartHidePrice;
    }

    /**
     * Add logic hide price when place order
     *
     * @param \Magento\Checkout\Helper\Data $subject
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePlaceOrder(
        $subject,
        $cartId,
        PaymentInterface $paymentMethod = null
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        $this->helperApi->customerGroupId = $quote->getCustomerGroupId();
        if (!$this->cartHidePrice->isPlaceOrder($quote)) {
            throw new LocalizedException(
                __('Please contact us for price.')
            );
        }
        return [$cartId, $paymentMethod];
    }
}
