<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Controller\Adminhtml;

abstract class PriceOptions extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'AutifyDigital_V12Finance::PriceOptions_menu';
    protected $_coreRegistry;

    protected $priceOptions;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \AutifyDigital\V12Finance\Model\PriceOptions $priceOptions
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->priceOptions = $priceOptions;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('AutifyDigital'), __('AutifyDigital'))
            ->addBreadcrumb(__('Price Options'), __('Price Options'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('AutifyDigital_V12Finance::PriceOptions_view');
    }
}

