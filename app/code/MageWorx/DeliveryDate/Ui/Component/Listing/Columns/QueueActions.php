<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class QueueActions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var OrderAddressRepositoryInterface
     */
    private $orderAddressRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OrderAddressRepositoryInterface $orderAddressRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder             = $urlBuilder;
        $this->orderAddressRepository = $orderAddressRepository;
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
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item['parent_id'])) {
                    $item[$this->getData('name')]['view_order'] = [
                        'href'   => $this->urlBuilder->getUrl(
                            'sales/order/view',
                            ['order_id' => $item['parent_id']]
                        ),
                        'label'  => __('View Order'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
