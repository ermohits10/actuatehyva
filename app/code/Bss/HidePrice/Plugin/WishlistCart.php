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
namespace Bss\HidePrice\Plugin;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class WishlistCart
 *
 * @package Bss\HidePrice\Plugin
 */
class WishlistCart
{
    /**
     * Data
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * ProductRepositoryInterface
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ItemFactory
     *
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * ResultFactory
     *
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * MessageManagerInterface
     *
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * WishlistCartController constructor.
     *
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $pr
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     * @param ResultFactory $resultFactory
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        ResultFactory $resultFactory,
        MessageManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->productRepository = $pr;
        $this->itemFactory = $itemFactory;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Not allow add cart if hideprice is enable
     *
     * @param object $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $itemId = $subject->getRequest()->getParam('item');
        $item = $this->itemFactory->create()->load($itemId);
        $product = $this->productRepository->getById($item->getProductId());
        if (!$this->helper->activeHidePrice($product)) {
            return $proceed();
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            return $resultRedirect;
        }
    }
}
