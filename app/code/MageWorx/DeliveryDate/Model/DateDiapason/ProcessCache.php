<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\DateDiapason;

use MageWorx\DeliveryDate\Api\Data;

class ProcessCache implements \MageWorx\DeliveryDate\Api\DateDiapasonCacheInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @inheritDoc
     */
    public function get(string $cacheId): ?\MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface
    {
        return $this->cache[$cacheId] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function set(string $cacheId, Data\DateDiapasonInterface $dateDiapason): void
    {
        $this->cache[$cacheId] = $dateDiapason;
    }
}
