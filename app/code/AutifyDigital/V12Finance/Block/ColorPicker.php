<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Block;

/**
 * Class ColorPicker
 * @package AutifyDigital\V12Finance\Block
 */
class ColorPicker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * add color picker in admin configuration fields
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string script
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $elementId = $("#' . $element->getHtmlId() . '");
                    $elementId.css("background-color", "'. $value .'");

                    $elementId.ColorPicker({
                        layout: "hex",
                        color: "'. $value .'",
                        onChange: function (hsb, hex, rgb) {
                            $elementId.css("background-color", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
            </script>';

        return $html;
    }
}
