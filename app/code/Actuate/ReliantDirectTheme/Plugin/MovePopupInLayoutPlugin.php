<?php

namespace Actuate\ReliantDirectTheme\Plugin;

use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\Event\Observer;

class MovePopupInLayoutPlugin
{
    private CurrentTheme $currentTheme;

    /**
     * @param CurrentTheme $currentTheme
     */
    public function __construct(
        CurrentTheme $currentTheme
    ) {
        $this->currentTheme = $currentTheme;
    }

    public function aroundExecute(
        \Amasty\GdprCookieHyva\Observer\MovePopupInLayout $subject,
        \Closure $proceed,
        Observer $observer
    ) {
        if ($this->currentTheme->isHyva()) {
            return $proceed($observer);
        }
        return $this;
    }
}
