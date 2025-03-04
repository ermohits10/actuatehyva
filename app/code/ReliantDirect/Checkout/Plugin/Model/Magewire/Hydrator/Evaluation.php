<?php

namespace ReliantDirect\Checkout\Plugin\Model\Magewire\Hydrator;

use Magento\Checkout\Model\Session as SessionCheckout;
use Hyva\Checkout\Model\Magewire\Hydrator\Evaluation as ParentEvaluation;

class Evaluation
{
    protected $sessionCheckout;

    public function __construct(
        SessionCheckout $sessionCheckout
    ) {
        $this->sessionCheckout = $sessionCheckout;
    }

    public function afterCanHydrate(
        ParentEvaluation $subject,
        $result,
        $component
    ) {
        if ($component->id == "checkout.terms-conditions" && $this->sessionCheckout->getIsIgnoreTermAndCondition()) {
            $result = false;
            $this->sessionCheckout->setIsIgnoreTermAndCondition(false);
        }

        return $result;
    }
}