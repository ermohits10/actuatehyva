<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Model\Config\Source;

class GetCronHour implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
	public function toOptionArray()
	{
		$options = [['value' => '', 'label' => __('-- Please Select --')]];
		for($i = 1; $i <= 12; $i++) {
            $options[] = ['value' => $i, 'label' => $i .' Month(s)'];
		}
        return $options;
	}
}
