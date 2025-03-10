<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class DeliveryOptionActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit']      = [
                    'href'   => $this->urlBuilder->getUrl(
                        'mageworx_deliverydate/deliveryoption/edit',
                        ['id' => $item['entity_id'], 'store' => $storeId]
                    ),
                    'label'  => __('Edit'),
                    'hidden' => false,
                ];
                $item[$this->getData('name')]['view_grid'] = [
                    'href'   => $this->urlBuilder->getUrl(
                        'mageworx_deliverydate/deliveryoption/queue_grid',
                        ['id' => $item['entity_id'], 'store' => $storeId]
                    ),
                    'label'  => __('View Queue Grid'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
