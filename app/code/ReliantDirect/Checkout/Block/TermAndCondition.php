<?php

namespace ReliantDirect\Checkout\Block;

class TermAndCondition extends \Magento\Framework\View\Element\Template
{
    protected function _toHtml()
    {
        $quoteActionBlock = $this->getLayout()->getBlock('component-messenger-quote-actions');
        $termsConditionsBlock = $this->getLayout()->getBlock('checkout.terms-conditions');

        $html = "";
        if ($quoteActionBlock) {
            $html .= $quoteActionBlock->toHtml();

        }
        if ($termsConditionsBlock) {
            $html .= $termsConditionsBlock->toHtml();
        }

        return $html;
    }
}
