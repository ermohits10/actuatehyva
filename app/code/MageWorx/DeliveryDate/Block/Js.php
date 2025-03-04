<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block;

use Magento\Framework\View\Element\Template;

class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * Js constructor.
     *
     * @param Template\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \MageWorx\DeliveryDate\Helper\Data $helper,
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->helper   = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getConfig()
    {
        $nowDate = new \DateTime(
            'now',
            new \DateTimeZone($this->timezone->getConfigTimezone())
        );

        $dateFormat          = $this->helper->getDateFormat();
        $timeLabelFormat     = $this->helper->getTimeLabelFormat();
        $commentFieldVisible = $this->helper->isCommentFieldVisible();

        $deliveryDateAdditionalTemplateData = $this->helper->getTemplateDataForDeliveryDateInput();
        $deliveryTimeAdditionalTemplateData = $this->helper->getTemplateDataForDeliveryTimeInput();

        $displayDatesWithExtraCharge = $this->helper->getDisplayDatesWithExtraCharge();

        return [
            'timezone'                  => $this->timezone->getConfigTimezone(),
            'now'                       => $nowDate->format('Y/m/d H:i:s'),
            'day_label_format'          => $dateFormat,
            'time_label_format'         => $timeLabelFormat,
            'comment_field_visible'     => $commentFieldVisible,
            'day'                       => $deliveryDateAdditionalTemplateData,
            'time'                      => $deliveryTimeAdditionalTemplateData,
            'display_with_extra_charge' => $displayDatesWithExtraCharge
        ];
    }
}
