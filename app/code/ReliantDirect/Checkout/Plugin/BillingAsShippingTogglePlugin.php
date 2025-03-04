<?php

namespace ReliantDirect\Checkout\Plugin;

use Hyva\Checkout\Magewire\Checkout\AddressView\BillingDetails;

class BillingAsShippingTogglePlugin
{
    public function afterToggleBillingAsShipping(BillingDetails $subject, $result)
    {
        $subject->dispatchBrowserEvent('billing-as-shipping-toggled', $result);
        return $result;
    }
}
