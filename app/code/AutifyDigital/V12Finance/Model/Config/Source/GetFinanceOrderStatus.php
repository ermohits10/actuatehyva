<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

/**
 * Order Statuses source model
 */
namespace AutifyDigital\V12Finance\Model\Config\Source;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class Status
 * @api
 * @since 100.0.2
 */
class GetFinanceOrderStatus implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * CollectionFactory
     *
     * @var CollectionFactory|CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct(CollectionFactory $statusCollectionFactory)
    {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();
        $optionArray = [];

        foreach ($options as $opt) {

            if (preg_match('#^finance#', $opt['value'])) {
                array_push($optionArray, $opt);
            }
        }

        return $optionArray;
    }
}
