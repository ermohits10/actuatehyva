<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Product\Form;

class DeliveryDateModifier extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $meta['delivery-options']['children']['mw_cut_off_time']['arguments']['data']['config']['component'] =
            'MageWorx_DeliveryDate/js/form/element/same_day_delivery_time';

        return $meta;
    }
}
