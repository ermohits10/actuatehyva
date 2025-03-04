<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Component;

class UpdateQueueForm extends \Magento\Ui\Component\Form
{
    /**
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        $data       = $this->getContext()->getDataProvider()->getData();
        $dataSource = [
            'data' => $data
        ];

        return $dataSource;
    }
}
