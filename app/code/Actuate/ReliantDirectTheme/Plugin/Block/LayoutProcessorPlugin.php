<?php

namespace Actuate\ReliantDirectTheme\Plugin\Block;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Options;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\AttributeMapper;

class LayoutProcessorPlugin
{
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayoutResult
    ): array {

        $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['notice'] = __('We\'ll send you delivery updates via text.');

        return $jsLayoutResult;
    }
}
