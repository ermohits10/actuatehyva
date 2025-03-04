<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\CheckoutConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;

class Renders implements ConfigProviderInterface
{
    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    private $helper;

    /**
     * Renders constructor.
     *
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     */
    public function __construct(
        \MageWorx\DeliveryDate\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $output = [];

        \Magento\Framework\Profiler::start('get_delivery_date_config_renders');

        if ($this->helper->isEnabled()) {
            try {
                $deliveryDateAdditionalTemplateData = $this->helper->getTemplateDataForDeliveryDateInput();
                $deliveryTimeAdditionalTemplateData = $this->helper->getTemplateDataForDeliveryTimeInput();
                $dateFormat                         = $this->helper->getDateFormat();
                $timeLabelFormat                    = $this->helper->getTimeLabelFormat();
                $commentFieldVisible                = $this->helper->isCommentFieldVisible();
                $firstDayIndex                      = $this->helper->getFirstDayIndex();

                $output['mageworx']['delivery_date'] = [
                    'day'                   => $deliveryDateAdditionalTemplateData,
                    'day_label_format'      => $dateFormat,
                    'time'                  => $deliveryTimeAdditionalTemplateData,
                    'time_label_format'     => $timeLabelFormat,
                    'comment_field_visible' => $commentFieldVisible,
                    'firstDay'              => $firstDayIndex
                ];
            } catch (\Exception $e) {
                \Magento\Framework\Profiler::stop('get_delivery_date_config_renders');

                return $output;
            }
        }

        \Magento\Framework\Profiler::stop('get_delivery_date_config_renders');

        return $output;
    }
}
