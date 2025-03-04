<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api\Data;

interface TimeLimitInterface
{
    /**
     * @return string|null
     */
    public function getFrom();

    /**
     * @param string|null $from
     * @return TimeLimitInterface
     */
    public function setFrom(string $from = null): TimeLimitInterface;

    /**
     * @return string|null
     */
    public function getTo();

    /**
     * @param string|null $to
     * @return TimeLimitInterface
     */
    public function setTo(string $to = null): TimeLimitInterface;

    /**
     * @return string|null
     */
    public function getExtraCharge();

    /**
     * @param string|null $extraCharge
     * @return TimeLimitInterface
     */
    public function setExtraCharge(string $extraCharge = null): TimeLimitInterface;
}
