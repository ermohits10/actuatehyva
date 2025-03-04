<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Exceptions;

class DeliveryTimeException extends \Exception
{
    protected $code = 1000;
    protected $message = 'Delivery Time Regular Exception. Must be catch by the DeliveryDate module.';
}
