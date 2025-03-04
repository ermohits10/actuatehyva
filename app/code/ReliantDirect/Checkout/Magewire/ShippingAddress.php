<?php

namespace ReliantDirect\Checkout\Magewire;

use Magewirephp\Magewire\Component;
use Hyva\Checkout\ViewModel\Checkout\AddressRenderer;

class ShippingAddress extends Component
{
    public $shippingAddress = "";
    protected $addressRender;

    /**
     * @param AddressRenderer $addressRender
     */
    public function __construct(AddressRenderer $addressRender)
    {
        $this->addressRender = $addressRender;
    }

    public function callShippingAddressRender()
    {
        $this->shippingAddress = $this->addressRender->renderShippingAddress();
    }
}
