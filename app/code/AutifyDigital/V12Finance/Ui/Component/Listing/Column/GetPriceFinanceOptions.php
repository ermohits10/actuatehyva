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

/**
 * Class GetPriceFinanceOptions
 * @package AutifyDigital\V12Finance\Ui\Component\Listing\Column
 */
class GetPriceFinanceOptions extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * GetFinanceOptions constructor.
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $financeName = $this->getFinanceName($item['finance_options']);
                $item['finance_options'] = $financeName;
            }
        }
        return $dataSource;
    }

    /**
     * @param $finaneOptions
     * @return string
     */
    public function getFinanceName($finaneOptions)
    {
        $explodedFinanceOption = explode(',', $finaneOptions);
        $financeName = '';

        foreach ($explodedFinanceOption as $finance) {
            $financeExplode  = explode('|', $finance);
            $financeName .= $financeExplode[2];
            if(sizeof($explodedFinanceOption) > 1) {
                $financeName .= ', ';
            }

        }
        return $financeName;
    }
}
