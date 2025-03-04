<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;

class DeliveryOptionShippingMethods extends Column
{
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $this->getContext()->getDataProvider()->addField(DeliveryOptionInterface::KEY_SHIPPING_METHODS_CHOICE_LIMITER);
        parent::prepare();
        $config = $this->getData('config');
        $config['options'][] = [
            'label' => __('All Shipping Methods'),
            'value' => [
                [
                    'label' => __('All Shipping Methods'),
                    'value' => 'all_shipping_methods',
                ]
            ]
        ];
        $this->setData('config', $config);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareItem(array $item)
    {
        if (!$item[DeliveryOptionInterface::KEY_SHIPPING_METHODS_CHOICE_LIMITER]) {
            return 'all_shipping_methods';
        }

        $content = $item[DeliveryOptionInterface::KEY_METHODS];

        return $content;
    }
}
