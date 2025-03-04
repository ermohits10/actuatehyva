<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Controller\Index;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use \AutifyDigital\V12Finance\Helper\Data;
use \Magento\Framework\Controller\ResultFactory;

/**
 * Class GetAnalyticsFrame
 * @package AutifyDigital\V12Finance\Controller\Index
 */
class GetAnalyticsFrame extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $httpRequest;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $httpHeader;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \AutifyDigital\V12Finance\Helper\Data $autifyDigitalHelper
     * @param \Magento\Framework\Registry $_coreRegistry
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest
     * @param \Magento\Framework\HTTP\Header $httpHeader
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ResultFactory $resultFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $_coreRegistry,
        Data $autifyDigitalHelper,
        \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest,
        \Magento\Framework\HTTP\Header $httpHeader
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        $this->_coreRegistry = $_coreRegistry;
        $this->httpRequest = $httpRequest;
        $this->httpHeader = $httpHeader;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->autifyDigitalHelper->addLog("Get Analytics Application Call");
        $this->autifyDigitalHelper->addLog($this->getRequest()->getParams(), true);
        if($this->getRequest()->getParams() && $this->getRequest()->getParam('SR') && $this->getRequest()->getParam('REF')) :
            $orderId = $this->getRequest()->getParam('SR');
            $applicationId = $this->getRequest()->getParam('REF');
            $applicationStatus = $this->getRequest()->getParam('Flag');
            $getApplicationById = $this->autifyDigitalHelper->getApplicationById($applicationId);

            if($getApplicationById->getOrderIncrementId() === $orderId && $applicationStatus == "30") :
                $order = $this->autifyDigitalHelper->getOrder($getApplicationById->getOrderId());

                $getGoogleMarketingConfig = $this->autifyDigitalHelper->getGoogleMarketingConfig();

                $googleAdsActive = $getGoogleMarketingConfig['google_adwords_active'];
                $googleAdsConversionId = $getGoogleMarketingConfig['google_adwords_conversion_id'];
                $googleAdsConversionLanguage = $getGoogleMarketingConfig['google_adwords_conversion_language'];
                $googleAdsConversionFormat = $getGoogleMarketingConfig['google_adwords_conversion_format'];
                $googleAdsConversionColor = $getGoogleMarketingConfig['google_adwords_conversion_color'];
                $googleAdsConversionLabel = $getGoogleMarketingConfig['google_adwords_conversion_label'];

                if($googleAdsActive && $googleAdsConversionId && $getApplicationById->getAdsSent() != 1) :
                    $adsString = '<script type="text/javascript">
                        /* <![CDATA[ */
                        var google_conversion_id = "' . $googleAdsConversionId .'";
                        var google_conversion_language = "' . $googleAdsConversionLanguage .'";
                        var google_conversion_format = "' . $googleAdsConversionFormat .'";
                        var google_conversion_color = "' . $googleAdsConversionColor .'";
                        var google_conversion_label = "' . $googleAdsConversionLabel .'";
                        var google_remarketing_only = false;
                        var google_conversion_value = "' . $order->getGrandTotal() .'";
                        /* ]]> */
                    </script>
                    <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
                    <noscript>
                        <div style="display:inline;">
                            <img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/' . $googleAdsConversionId .'/?label=' . $googleAdsConversionLabel .'&amp;value=' . $order->getGrandTotal() .'&amp;guid=ON&amp;script=0"/>
                        </div>
                    </noscript>';
                    $getApplicationById->setAdsSent(1)->save();
                    echo $adsString;exit;
                endif;
            
                return '';
            else:
                $this->autifyDigitalHelper->addLog('Wrong Analytics Call', true);
            endif;
        else:
            return '';
        endif;
    }
}

