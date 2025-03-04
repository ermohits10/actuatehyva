<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model;

use MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface;

class DateDiapason implements \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface
{
    /**
     * @var \DateTimeInterface|null
     */
    protected $minDate;

    /**
     * @var \DateTimeInterface|null
     */
    protected $maxDate;

    /**
     * @inheritDoc
     */
    public function getMinDate(): ?\DateTimeInterface
    {
        return $this->minDate;
    }

    /**
     * @inheritDoc
     */
    public function getMaxDate(): ?\DateTimeInterface
    {
        return $this->maxDate;
    }

    /**
     * @inheritDoc
     */
    public function setMinDate(\DateTimeInterface $date = null): DateDiapasonInterface
    {
        $this->minDate = $date;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMaxDate(\DateTimeInterface $date = null): DateDiapasonInterface
    {
        $this->maxDate = $date;

        return $this;
    }
}
