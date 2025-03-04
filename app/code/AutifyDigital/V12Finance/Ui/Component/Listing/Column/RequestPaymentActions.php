<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */


namespace AutifyDigital\V12Finance\Ui\Component\Listing\Column;

class RequestPaymentActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const URL_PATH_REQUEST_PAYMENT = 'autifydigital_v12finance/application/requestpayment';

    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
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
            foreach ($dataSource['data']['items'] as & $item) {
                // echo "<pre>";
                // print_r($item);

                if (isset($item['application_id']) && isset($item['application_status']) && $item['application_status'] == '5') {
                    $item[$this->getData('name')] = [
                        'request_payment' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_REQUEST_PAYMENT,
                                [
                                    'application_id' => $item['finance_application_id']
                                ]
                            ),
                            'label' => __('Request Payment')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}

