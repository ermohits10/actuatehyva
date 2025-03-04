<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Block;

use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MageWorx\DeliveryDate\Helper\Data as Helper;

/**
 * Delivery date general configuration for frontend. Includes date settings, renderer setting and other global settings.
 */
class JsConfig extends Template
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * Js configuration class constructor.
     *
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param LocaleResolver $resolver
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context           $context,
        TimezoneInterface $timezone,
        LocaleResolver    $localeResolver,
        Helper            $helper,
        array             $data = []
    ) {
        $this->timezone       = $timezone;
        $this->localeResolver = $localeResolver;
        $this->helper         = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getConfig(): array
    {
        $nowDate = new \DateTime(
            'now',
            new \DateTimeZone($this->timezone->getConfigTimezone())
        );

        $config = [
            'enabled'                   => $this->helper->isEnabled(),
            'timezone'                  => $this->timezone->getConfigTimezone(),
            'now'                       => $nowDate->format('Y/m/d H:i:s'),
            'server_timezone_offset'    => $this->timezone->date()->getOffset(),
            'locale'                    => $this->getSimplifiedLocale(),
            'day_label_format'          => $this->helper->getDateFormat(),
            'time_label_format'         => $this->helper->getTimeLabelFormat() ?? "{{from_time_24}} - {{to_time_24}}",
            'day_input_label'           => $this->helper->getDeliveryDateTitle(),
            'time_input_label'          => $this->helper->getDeliveryTimeTitle(),
            'comment_field_visible'     => $this->helper->isCommentFieldVisible(),
            'comment_field_label'       => $this->helper->getCommentFieldLabel(),
            'day'                       => $this->helper->getTemplateDataForDeliveryDateInput(),
            'time'                      => $this->helper->getTemplateDataForDeliveryTimeInput(),
            'display_with_extra_charge' => $this->helper->getDisplayDatesWithExtraCharge(),
            'required'                  => $this->helper->isDeliveryDateRequired(),
            'preselected'               => $this->helper->isPreSelectDeliveryDateEnabled(),
            'firstDay'                  => $this->helper->getFirstDayIndex(),
            'reload_shipping_rates'     => $this->helper->reloadShippingRatesAfterExtraChargeApplied()
        ];

        $this->_eventManager->dispatch(
            'mw_delivery_date_config_assembled',
            [
                'config' => &$config
            ]
        );

        return $config;
    }

    /**
     * Get locale string like en, ru, fr
     *
     * @return string
     */
    public function getSimplifiedLocale(): string
    {
        $baseLocale = $this->localeResolver->getLocale();
        $localePart = mb_substr($baseLocale, 0, -2);

        return mb_strtolower($localePart);
    }
}
