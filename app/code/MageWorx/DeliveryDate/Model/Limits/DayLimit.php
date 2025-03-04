<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Limits;

use MageWorx\DeliveryDate\Api\Data\DayLimitInterface;
use MageWorx\DeliveryDate\Api\Data\TimeLimitInterface;

class DayLimit implements DayLimitInterface
{
    /**
     * @var int
     */
    protected $dayIndex;

    /**
     * @var string
     */
    protected $dateFormatted;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var TimeLimitInterface[]
     */
    protected $timeLimits;

    /**
     * @var float
     */
    protected $extraCharge = 0;

    /**
     * @var string
     */
    protected $extraChargeMessage = '';

    /**
     * @return int
     */
    public function getDayIndex()
    {
        return $this->dayIndex;
    }

    /**
     * @param int $dayIndex
     * @return DayLimitInterface
     */
    public function setDayIndex(int $dayIndex = null): DayLimitInterface
    {
        $this->dayIndex = $dayIndex;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormatted()
    {
        return $this->dateFormatted;
    }

    /**
     * @param string $dateFormatted
     * @return DayLimitInterface
     */
    public function setDateFormatted(string $dateFormatted = null): DayLimitInterface
    {
        $this->dateFormatted = $dateFormatted;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return DayLimitInterface
     */
    public function setDate(string $date = null): DayLimitInterface
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return TimeLimitInterface[]
     */
    public function getTimeLimits(): array
    {
        return $this->timeLimits;
    }

    /**
     * @param TimeLimitInterface[] $timeLimits
     * @return DayLimitInterface
     */
    public function setTimeLimits(array $timeLimits): DayLimitInterface
    {
        $this->timeLimits = $timeLimits;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getExtraCharge(): ?float
    {
        return $this->extraCharge;
    }

    /**
     * @param float $value
     * @return DayLimitInterface
     */
    public function setExtraCharge(float $value = 0): DayLimitInterface
    {
        $this->extraCharge = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraChargeMessage(): string
    {
        return $this->extraChargeMessage;
    }

    /**
     * @param string $message
     * @return DayLimitInterface
     */
    public function setExtraChargeMessage(string $message = ''): DayLimitInterface
    {
        $this->extraChargeMessage = $message;

        return $this;
    }
}
