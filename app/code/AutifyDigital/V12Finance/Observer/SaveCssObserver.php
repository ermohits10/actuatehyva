<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Observer;

use AutifyDigital\V12Finance\Helper\Data;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class SaveCssObserver
 * @package AutifyDigital\V12Finance\Observer
 */
class SaveCssObserver implements ObserverInterface
{
    /**
     * @var \AutifyDigital\V12Finance\Helper\Data
     */
    protected $autifyDigitalHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * CssObserver constructor.
     * @param Data $autifyDigitalHelper
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        Data $autifyDigitalHelper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->autifyDigitalHelper = $autifyDigitalHelper;
        $this->filesystem = $filesystem;
    }

    public function execute(EventObserver $observer)
    {
        if($observer->getEvent()) :

            $cssDesign = $this->autifyDigitalHelper->getAllDesignConfig();
            $media = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $contents = "";
            if($cssDesign['font_size']) {
                $contents .= ".finance-calculator, .finance-view-popup, .v12finance-checkout{ font-size: " . $cssDesign['font_size'] . "px !important; }";
            }

            if($cssDesign['font_color']) {
                $contents .= ".finance-calculator, .finance-view-popup, .v12finance-checkout{ color: " . $cssDesign['font_color'] . " !important;}";
            }

            if($cssDesign['button_color']) {
                $contents .= ".finance-view-popup button, .v12finance-checkout button{ color: " . $cssDesign['button_color'] . " !important; }";
            }

            if($cssDesign['button_background_color']) {
                $contents .= ".finance-view-popup button, .v12finance-checkout button{ background: " . $cssDesign['button_background_color'] . " !important; border: 1px solid " . $cssDesign['button_background_color'] . " !important; }";
            }

            if($cssDesign['button_hover_color']) {
                $contents .= ".finance-view-popup button:hover, .v12finance-checkout button:hover{ color: " . $cssDesign['button_hover_color'] . "  !important; }";
            }
            if($cssDesign['button_background_hover_color']) {
                $contents .= ".finance-view-popup button:hover, .v12finance-checkout button:hover{ background: " . $cssDesign['button_background_hover_color'] . "  !important; border: 1px solid " . $cssDesign['button_background_hover_color'] . " !important; }";
            }

            if($cssDesign['button_color']) {
                $contents .= "#apply-finance button, #v12-finance-placeorder-click button{ color: " . $cssDesign['button_color'] . "  !important; }";
            }

            if($cssDesign['button_background_color']) {
                $contents .= "#apply-finance button, #v12-finance-placeorder-click button{ background: " . $cssDesign['button_background_color'] . "  !important; border: 1px solid " . $cssDesign['button_background_color'] . "  !important; }";
            }

            if($cssDesign['button_hover_color']) {
                $contents .= "#apply-finance button:hover, #v12-finance-placeorder-click button:hover{ color: " . $cssDesign['button_hover_color'] . "  !important; }";
            }

            if($cssDesign['button_background_hover_color']) {
                $contents .= "#apply-finance button:hover, #v12-finance-placeorder-click button:hover{ background: " . $cssDesign['button_background_hover_color'] . "  !important; border: 1px solid " . $cssDesign['button_background_hover_color'] . " !important; }";
            }

            $contents .= $cssDesign['custom_css'];
            $media->writeFile("autifydigital/v12finance.css", $contents);

        endif;
    }
}
