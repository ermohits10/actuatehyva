<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class DeliveryTime extends Column
{
    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface                   $context,
        UiComponentFactory                 $uiComponentFactory,
        \MageWorx\DeliveryDate\Helper\Data $helper,
        array                              $components = [],
        array                              $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $hoursFrom   = $item['delivery_hours_from'] ?? 0;
                $minutesFrom = $item['delivery_minutes_from'] ?? 0;
                $hoursTo     = $item['delivery_hours_to'] ?? 0;
                $minutesTo   = $item['delivery_minutes_to'] ?? 0;

                if (array_sum([$hoursFrom, $minutesFrom, $hoursTo, $minutesTo]) > 0) {
                    $from = $hoursFrom . QueueDataInterface::TIME_DELIMITER . $minutesFrom;
                    $to   = $hoursTo . QueueDataInterface::TIME_DELIMITER . $minutesTo;

                    $item[$this->getData('name')] = $this->helper->getDeliveryTimeFormattedMessage($from, $to);
                } else {
                    $item[$this->getData('name')] = __('No Time');
                }
            }
        }

        return $dataSource;
    }
}
