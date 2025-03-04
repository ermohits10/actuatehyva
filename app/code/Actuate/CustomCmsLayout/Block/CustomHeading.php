<?php

namespace Actuate\CustomCmsLayout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;

class CustomHeading extends Template
{
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }
}