<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api\Data;

/**
 * Interface DateDiapasonInterface
 *
 * Date diapason
 */
interface DateDiapasonInterface
{
    /**
     * Get minimal date from diapason
     *
     * @return \DateTimeInterface|null
     */
    public function getMinDate(): ?\DateTimeInterface;

    /**
     * Get maximal date from diapason
     *
     * @return \DateTimeInterface|null
     */
    public function getMaxDate(): ?\DateTimeInterface;

    /**
     * Set minimal date
     *
     * @param \DateTimeInterface|null $date
     * @return DateDiapasonInterface
     */
    public function setMinDate(\DateTimeInterface $date = null): DateDiapasonInterface;

    /**
     * Set maximal date
     *
     * @param \DateTimeInterface|null $date
     * @return DateDiapasonInterface
     */
    public function setMaxDate(\DateTimeInterface $date = null): DateDiapasonInterface;
}
