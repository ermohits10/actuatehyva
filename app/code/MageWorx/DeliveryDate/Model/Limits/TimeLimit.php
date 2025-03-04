<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Limits;

use MageWorx\DeliveryDate\Api\Data\TimeLimitInterface;

class TimeLimit implements TimeLimitInterface
{
    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $extraCharge;

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return TimeLimitInterface
     */
    public function setFrom(string $from = null): TimeLimitInterface
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return TimeLimitInterface
     */
    public function setTo(string $to = null): TimeLimitInterface
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraCharge()
    {
        return $this->extraCharge;
    }

    /**
     * @param string $extraCharge
     * @return TimeLimitInterface
     */
    public function setExtraCharge(string $extraCharge = null): TimeLimitInterface
    {
        $this->extraCharge = $extraCharge;

        return $this;
    }
}
