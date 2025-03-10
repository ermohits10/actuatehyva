<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Integrator
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Cron;

/**
 * Class Cleaner
 * @package Ced\Integrator\Cron
 */
class Cleaner implements \Ced\Integrator\Cron\CleanerInterface
{
    /** @var \Ced\Integrator\Model\Cleaner  */
    public $cleaner;

    public function __construct(
        \Ced\Integrator\Model\Cleaner $cleaner
    ) {
        $this->cleaner = $cleaner;
    }

    public function execute()
    {
        //$this->cleaner->clean();
    }
}
