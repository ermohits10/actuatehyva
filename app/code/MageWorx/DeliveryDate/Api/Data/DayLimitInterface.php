<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api\Data;

interface DayLimitInterface
{
    /**
     * @return int|null
     */
    public function getDayIndex();

    /**
     * @param int|null $dayIndex
     * @return $this
     */
    public function setDayIndex(int $dayIndex = null): DayLimitInterface;

    /**
     * @return string|null
     */
    public function getDateFormatted();

    /**
     * @param string|null $dateFormatted
     * @return void
     */
    public function setDateFormatted(string $dateFormatted = null): DayLimitInterface;

    /**
     * @return string|null
     */
    public function getDate();

    /**
     * @param string|null $date
     * @return void
     */
    public function setDate(string $date = null): DayLimitInterface;

    /**
     * @return \MageWorx\DeliveryDate\Api\Data\TimeLimitInterface[]
     */
    public function getTimeLimits(): array;

    /**
     * @param \MageWorx\DeliveryDate\Api\Data\TimeLimitInterface[] $timeLimits
     * @return void
     */
    public function setTimeLimits(array $timeLimits): DayLimitInterface;

    /**
     * @return float|null
     */
    public function getExtraCharge(): ?float;

    /**
     * @param float $value
     * @return DayLimitInterface
     */
    public function setExtraCharge(float $value = 0): DayLimitInterface;

    /**
     * @return string
     */
    public function getExtraChargeMessage(): string;

    /**
     * @param string $message
     * @return DayLimitInterface
     */
    public function setExtraChargeMessage(string $message = ''): DayLimitInterface;
}
