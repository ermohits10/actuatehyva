<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Api;

interface DateDiapasonCacheInterface
{
    /**
     * @param string $cacheId
     * @return Data\DateDiapasonInterface|null
     */
    public function get(string $cacheId): ?\MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface;

    /**
     * @param string $cacheId
     * @param Data\DateDiapasonInterface $dateDiapason
     */
    public function set(string $cacheId, \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface $dateDiapason): void;
}
