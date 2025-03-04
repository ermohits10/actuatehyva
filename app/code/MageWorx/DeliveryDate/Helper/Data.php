<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Helper;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\OrderAddressExtensionInterface;
use Magento\Store\Model\ScopeInterface;
use MageWorx\DeliveryDate\Api\Data\DeliveryDateDataInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use MageWorx\DeliveryDate\Model\DeliveryOption;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'delivery_date/main/enabled';

    const XML_PATH_INCLUDE_DELIVERY_DATES_RESERVED_BY_UNACCOMPLISHED_ORDERS =
        'delivery_date/main/include_delivery_dates_reserved_by_unaccomplished_orders';
    const XML_PATH_INCLUDE_NON_WORKING_DAYS_IN_PROCESSING_PERIOD            =
        'delivery_date/main/include_non_working_days_in_processing_order_period';

    const XML_PATH_DATE_TEMPLATE     = 'delivery_date/visualisation/date_template';
    const XML_PATH_TIME_TEMPLATE     = 'delivery_date/visualisation/time_template';
    const XML_PATH_TIME_LABEL_FORMAT = 'delivery_date/visualisation/time_label_template';

    const XML_PATH_DATE_FORMAT               = 'delivery_date/visualisation/date_format';
    const XML_PATH_DATE_FORMAT_CUSTOM        = 'delivery_date/visualisation/date_format_custom';
    const XML_PATH_REPLACE_DATE_TO_WORDS     = 'delivery_date/visualisation/replace_date_to_words';
    const XML_PATH_DATE_CALENDAR_PLACEHOLDER = 'delivery_date/visualisation/delivery_date_placeholder';

    const XML_PATH_QUOTE_LIMITATION_MODE    = 'delivery_date/main/quote_limitation_mode';
    const QUOTE_LIMITATION_MODE_OVERLOADING = 0;
    const QUOTE_LIMITATION_MODE_RESTRICTION = 1;

    const XML_PATH_GENERAL_QUEUE         = 'delivery_date/main/general_queue';
    const XML_PATH_COMMENT_FIELD_VISIBLE = 'delivery_date/main/comment_field_visible';
    const XML_PATH_COMMENT_FIELD_LABEL   = 'delivery_date/main/comment_field_label';

    const XML_PATH_DEFAULT_DELIVERY_OPTION_ID = 'delivery_date/main/default_delivery_option_id';
    const XML_PATH_DELIVERY_DATE_REQUIRED     = 'delivery_date/main/delivery_date_required';
    const XML_PATH_PRE_SELECT_DELIVERY_DATE   = 'delivery_date/main/pre_select_delivery_date';
    const XML_PATH_SET_DEFAULT_DELIVERY_DATE  = 'delivery_date/main/set_default_date_enabled';
    const XML_PATH_RELOAD_SHIPPING_RATES      = 'delivery_date/main/reload_shipping_rates_on_price_change';

    const XML_PATH_DELIVERY_DATE_TITLE = 'delivery_date/visualisation/delivery_date_title';
    const XML_PATH_DELIVERY_TIME_TITLE = 'delivery_date/visualisation/delivery_time_title';

    const XML_PATH_DISPLAY_EMPTY_DD_BLOCK_IN_ADMIN =
        'delivery_date/visualisation/display_empty_delivery_date_block_in_order_view_page';

    const XML_PATH_USE_MIN_DELIVERY_DAYS_FOM_PRODUCT     = 'delivery_date/product/use_min_time';
    const XML_PATH_LIMIT_BY_ESTIMATED_DELIVERY_PERIOD_TO = 'delivery_date/product/limit_by_edt_to';
    const XML_PATH_BLOCK_WHEN_DISABLED_ON_ANY_PRODUCT    = 'delivery_date/product/block_if_on_any_disabled';
    const XML_PATH_EDT_MESSAGE_FORMAT_FOR_PRODUCT        = 'delivery_date/product/message_format';
    const XML_PATH_EDT_SAME_DAY_DELIVERY_MESSAGE         = 'delivery_date/product/same_day_delivery_message_format';
    const XML_PATH_EDT_NEXT_DAY_DELIVERY_MESSAGE         = 'delivery_date/product/next_day_delivery_message_format';
    const XML_PATH_USE_STOCK                             = 'delivery_date/product/use_stock';
    const XML_PATH_ERROR_MESSAGE                         = 'delivery_date/product/error_message';
    const XML_PATH_DISPLAY_SELECT_OPTIONS_MESSAGE        = 'delivery_date/product/display_select_options_message';
    const XML_PATH_SELECT_OPTIONS_MESSAGE                = 'delivery_date/product/select_options_message';
    const XML_PATH_UNAVAILABLE_DELIVERY_ACTION           = 'delivery_date/product/unavailable_delivery_action';

    const XML_PATH_DEFAULT_EMAIL_DATA      =
        'delivery_date/visualisation/use_default_delivery_date_data_in_emails';
    const XML_PATH_CHANGE_DATE_BY_CUSTOMER =
        'delivery_date/main/change_date_by_customer_enabled';

    const XML_PATH_DISPLAY_DATES_WITH_EXTRA_CHARGE_IN_CUSTOMER_ACCOUNT =
        'delivery_date/main/display_dates_with_extra_charge';

    const GENERAL_QUEUE_GLOBAL              = 1;
    const GENERAL_QUEUE_PER_DELIVERY_OPTION = 0;

    const MIN_DAYS_RANGE = -30;
    const MAX_DAYS_RANGE = 90;

    const PRODUCT_EDT_DAYS_FROM_NUMBER_VARIABLE   = '{{days_from_number}}';
    const PRODUCT_EDT_DAYS_TO_NUMBER_VARIABLE     = '{{days_to_number}}';
    const PRODUCT_EDT_DAYS_FROM_CALENDAR_VARIABLE = '{{days_from_calendar}}';
    const PRODUCT_EDT_DAYS_TO_CALENDAR_VARIABLE   = '{{days_to_calendar}}';
    const PRODUCT_EDT_MONTH_FROM                  = '{{month_from}}';
    const PRODUCT_EDT_MONTH_TO                    = '{{month_to}}';

    /**
     * @var \MageWorx\DeliveryDate\Model\Source\TemplatesSource
     */
    private $deliveryDateTemplatesSource;

    /**
     * @var \MageWorx\DeliveryDate\Model\Source\TemplatesSource
     */
    private $deliveryTimeTemplatesSource;

    /**
     * @var \MageWorx\DeliveryDate\Model\Source\WorkingDays
     */
    private $workingDaysSourceModel;

    /**
     * @var \Magento\Directory\Model\PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolver;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \MageWorx\DeliveryDate\Model\Source\TemplatesSource $deliveryDateTemplates
     * @param \MageWorx\DeliveryDate\Model\Source\TemplatesSource $deliveryTimeTemplates
     * @param \MageWorx\DeliveryDate\Model\Source\WorkingDays $workingDaysSourceModel
     * @param \Magento\Directory\Model\PriceCurrency $priceCurrency
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        Context                                              $context,
        \MageWorx\DeliveryDate\Model\Source\TemplatesSource  $deliveryDateTemplates,
        \MageWorx\DeliveryDate\Model\Source\TemplatesSource  $deliveryTimeTemplates,
        \MageWorx\DeliveryDate\Model\Source\WorkingDays      $workingDaysSourceModel,
        \Magento\Directory\Model\PriceCurrency               $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Locale\ResolverInterface          $resolver
    ) {
        parent::__construct($context);
        $this->deliveryDateTemplatesSource = $deliveryDateTemplates;
        $this->deliveryTimeTemplatesSource = $deliveryTimeTemplates;
        $this->workingDaysSourceModel      = $workingDaysSourceModel;
        $this->priceCurrency               = $priceCurrency;
        $this->timezone                    = $timezone;
        $this->resolver                    = $resolver;
    }

    /**
     * Is delivery date changing available from the customer account
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getChangeDateByCustomerEnabled(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CHANGE_DATE_BY_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is delivery dates with extra charge available on the customer account form (order edit)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getDisplayDatesWithExtraCharge(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_DATES_WITH_EXTRA_CHARGE_IN_CUSTOMER_ACCOUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isDefaultDeliveryDateEmailOutputEnabled(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEFAULT_EMAIL_DATA,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getQuoteLimitationMode(int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_QUOTE_LIMITATION_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get quote limitation mode: is need to restrict an order when limits exceeded?
     *
     * @param int|null $storeId
     * @return bool
     */
    public function includeDeliveryDatesReservedByUnaccomplishedOrders(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INCLUDE_DELIVERY_DATES_RESERVED_BY_UNACCOMPLISHED_ORDERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getTemplateDataForDeliveryDateInput(int $storeId = null): array
    {
        $selectedTemplate = $this->scopeConfig->getValue(
            self::XML_PATH_DATE_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $data               = $this->deliveryDateTemplatesSource->getTemplateData($selectedTemplate);
        $data['input_type'] = $selectedTemplate;
        if (!$this->isPreSelectDeliveryDateEnabled($storeId)) {
            $data['placeholder'] = $this->getCalendarDayPlaceholder($storeId);
        }

        return $data;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCalendarDayPlaceholder(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DATE_CALENDAR_PLACEHOLDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getTemplateDataForDeliveryTimeInput(int $storeId = null): array
    {
        $selectedTemplate = $this->scopeConfig->getValue(
            self::XML_PATH_TIME_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $data               = $this->deliveryTimeTemplatesSource->getTemplateData($selectedTemplate);
        $data['input_type'] = $selectedTemplate;

        return $data;
    }

    /**
     * Get formatted delivery time message
     *
     * @param int|string|null $from
     * @param int|string|null $to
     * @param int|string|null $storeId
     * @return string
     */
    public function getDeliveryTimeFormattedMessage($from = null, $to = null, int $storeId = null): ?string
    {
        $deliveryTimeFrom = $from ?? '00:00';
        $deliveryTimeTo   = $to ?? '24:00';

        $deliveryTimeMessageFormatted = lcfirst(
            $this->formatTimeAccordingTimeTemplate(
                $deliveryTimeFrom,
                $deliveryTimeTo,
                $storeId
            )
        );

        return strip_tags($deliveryTimeMessageFormatted);
    }

    /**
     * Format delivery time diapason string according selected template
     *
     * @param null|float $from
     * @param null|float $to
     * @param null|int $storeId
     * @return string
     */
    public function formatTimeAccordingTimeTemplate($from = null, $to = null, int $storeId = null): ?string
    {
        $format = $this->getTimeLabelFormat($storeId);

        $fromHour = $this->getPart($from, 0);
        if ($fromHour === null) {
            $fromHour = 0;
        }
        $fromMinute = $this->getPart($from, 1);
        if ($fromMinute === null) {
            $fromMinute = 0;
        }

        if (mb_strlen($fromMinute) === 1) {
            $fromMinute = '0' . $fromMinute;
        }

        $toHour = $this->getPart($to, 0);
        if ($toHour === null) {
            $toHour = 0;
        }

        $toMinutes = $this->getPart($to, 1);
        if ($toMinutes === null) {
            $toMinutes = 0;
        }

        if (mb_strlen($toMinutes) === 1) {
            $toMinutes = '0' . $toMinutes;
        }

        if ($fromHour == 12) {
            $from12 = 12 . QueueDataInterface::TIME_DELIMITER . $fromMinute . ' p.m.';
        } elseif ($fromHour == 24 || $fromHour == 0) {
            $from12 = 12 . QueueDataInterface::TIME_DELIMITER . $fromMinute . ' a.m.';
        } else {
            $from12 = $fromHour < 12 ?
                $fromHour . QueueDataInterface::TIME_DELIMITER . $fromMinute . ' a.m.' :
                ($fromHour - 12) . QueueDataInterface::TIME_DELIMITER . $fromMinute . ' p.m.';
        }

        if ($toHour == 12) {
            $to12 = 12 . QueueDataInterface::TIME_DELIMITER . $toMinutes . ' p.m.';
        } elseif ($toHour == 24 || $toHour == 0) {
            $to12 = 12 . QueueDataInterface::TIME_DELIMITER . $toMinutes . ' a.m.';
        } else {
            $to12 = $toHour < 12 ?
                $toHour . QueueDataInterface::TIME_DELIMITER . $toMinutes . ' a.m.' :
                ($toHour - 12) . QueueDataInterface::TIME_DELIMITER . $toMinutes . ' p.m.';
        }

        $from24 = ($fromHour >= 10 ? $fromHour : '0' . $fromHour) . QueueDataInterface::TIME_DELIMITER . $fromMinute;
        $to24   = ($toHour >= 10 ? $toHour : '0' . $toHour) . QueueDataInterface::TIME_DELIMITER . $toMinutes;

        $formattedString = $format;
        $formattedString = str_replace('{{from_time_24}}', $from24, $formattedString);
        $formattedString = str_replace('{{to_time_24}}', $to24, $formattedString);
        $formattedString = str_replace('{{from_time_12}}', $from12, $formattedString);
        $formattedString = str_replace('{{to_time_12}}', $to12, $formattedString);

        return $formattedString;
    }

    /**
     * Parse delivery date and time from extension attributes object
     *
     * @param ExtensionAttributesInterface|OrderAddressExtensionInterface $shippingAddressExtensionAttributes
     * @return array|null
     */
    public function parseDeliveryDateArray(ExtensionAttributesInterface $shippingAddressExtensionAttributes): ?array
    {
        if ($shippingAddressExtensionAttributes->getDeliveryHoursFrom() == 0 &&
            $shippingAddressExtensionAttributes->getDeliveryHoursTo() == 0 &&
            $shippingAddressExtensionAttributes->getDeliveryMinutesFrom() == 0 &&
            $shippingAddressExtensionAttributes->getDeliveryMinutesTo() == 0) {
            return null;
        }

        $deliveryHoursFrom = $shippingAddressExtensionAttributes->getDeliveryHoursFrom() ?? '00';

        $deliveryMinutesFrom = $shippingAddressExtensionAttributes->getDeliveryMinutesFrom() ?? '00';
        if (mb_strlen($deliveryMinutesFrom) === 1) {
            $deliveryMinutesFrom = '0' . $deliveryMinutesFrom;
        }

        $deliveryHoursTo = $shippingAddressExtensionAttributes->getDeliveryHoursTo() ?? '00';

        $deliveryMinutesTo = $shippingAddressExtensionAttributes->getDeliveryMinutesTo() ?? '00';
        if (mb_strlen($deliveryMinutesTo) === 1) {
            $deliveryMinutesTo = '0' . $deliveryMinutesTo;
        }

        $deliveryTimeFrom = implode(QueueDataInterface::TIME_DELIMITER, [$deliveryHoursFrom, $deliveryMinutesFrom]);
        $deliveryTimeTo   = implode(QueueDataInterface::TIME_DELIMITER, [$deliveryHoursTo, $deliveryMinutesTo]);

        return [
            'from' => [
                'in_minutes' => $deliveryHoursFrom * 60 + $deliveryMinutesFrom,
                'full'       => $deliveryTimeFrom,
                'hours'      => $deliveryHoursFrom,
                'minutes'    => $deliveryMinutesFrom
            ],
            'to'   => [
                'in_minutes' => $deliveryHoursTo * 60 + $deliveryMinutesTo,
                'full'       => $deliveryTimeTo,
                'hours'      => $deliveryHoursTo,
                'minutes'    => $deliveryMinutesTo
            ]
        ];
    }

    /**
     * Parse a delivery time parts from template
     *
     * @param array $timeLimitTemplate
     * @return array
     */
    public function parseFromToPartsFromTimeLimitTemplate(array $timeLimitTemplate): array
    {
        $from = $timeLimitTemplate['from'] === null ? DeliveryOption::MIN_TIME_IN_DAY : $timeLimitTemplate['from'];
        $to   = $timeLimitTemplate['to'] === null ? DeliveryOption::MAX_TIME_IN_DAY : $timeLimitTemplate['to'];

        $deliveryHoursFrom   = $this->getPart($from, 0);
        $deliveryMinutesFrom = $this->getPart($from, 1);

        $deliveryHoursTo   = $this->getPart($to, 0);
        $deliveryMinutesTo = $this->getPart($to, 1);

        $deliveryTimeFrom = implode(QueueDataInterface::TIME_DELIMITER, [$deliveryHoursFrom, $deliveryMinutesFrom]);
        $deliveryTimeTo   = implode(QueueDataInterface::TIME_DELIMITER, [$deliveryHoursTo, $deliveryMinutesTo]);

        return [
            'from' => [
                'in_minutes' => $deliveryHoursFrom * 60 + $deliveryMinutesFrom,
                'full'       => $deliveryTimeFrom,
                'hours'      => $deliveryHoursFrom,
                'minutes'    => $deliveryMinutesFrom
            ],
            'to'   => [
                'in_minutes' => $deliveryHoursTo * 60 + $deliveryMinutesTo,
                'full'       => $deliveryTimeTo,
                'hours'      => $deliveryHoursTo,
                'minutes'    => $deliveryMinutesTo
            ]
        ];
    }

    /**
     * @param string $timeString
     * @return array[]
     */
    public function parseFromToPartsFromTimeString(string $timeString): array
    {
        [$from, $to] = explode('_', $timeString);

        $deliveryHoursFrom   = $this->getPart($from, 0);
        $deliveryMinutesFrom = $this->getPart($from, 1);

        $deliveryHoursTo   = $this->getPart($to, 0);
        $deliveryMinutesTo = $this->getPart($to, 1);

        $deliveryTimeFrom = implode(QueueDataInterface::TIME_DELIMITER, [$deliveryHoursFrom, $deliveryMinutesFrom]);
        $deliveryTimeTo   = implode(QueueDataInterface::TIME_DELIMITER, [$deliveryHoursTo, $deliveryMinutesTo]);

        return [
            'from' => [
                'in_minutes' => $deliveryHoursFrom * 60 + $deliveryMinutesFrom,
                'full'       => $deliveryTimeFrom,
                'hours'      => $deliveryHoursFrom,
                'minutes'    => $deliveryMinutesFrom
            ],
            'to'   => [
                'in_minutes' => $deliveryHoursTo * 60 + $deliveryMinutesTo,
                'full'       => $deliveryTimeTo,
                'hours'      => $deliveryHoursTo,
                'minutes'    => $deliveryMinutesTo
            ]
        ];
    }

    /**
     * Convert time string (20:00) to minutes int (1200)
     *
     * @param string $time
     * @return int
     */
    public function convertTimeStringToMinutes(string $time): int
    {
        // Case when time without minutes
        if (stripos($time, QueueDataInterface::TIME_DELIMITER) === false) {
            return (int)$time * 60;
        }

        [$hours, $minutes] = explode(QueueDataInterface::TIME_DELIMITER, $time);

        return (int)$hours * 60 + (int)$minutes;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getTimeLabelFormat(int $storeId = null): ?string
    {
        return strip_tags(
            $this->scopeConfig->getValue(
                self::XML_PATH_TIME_LABEL_FORMAT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Is general queue used
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isGeneralQueueUsed(int $storeId = null): bool
    {
        $queueType = (int)$this->scopeConfig->getValue(
            self::XML_PATH_GENERAL_QUEUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $queueType == static::GENERAL_QUEUE_GLOBAL;
    }

    /**
     * Is comment field visible on the checkout
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isCommentFieldVisible(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENT_FIELD_VISIBLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getCommentFieldLabel(int $storeId = null): ?string
    {
        return strip_tags(
            $this->scopeConfig->getValue(
                self::XML_PATH_COMMENT_FIELD_LABEL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Get selected date format
     *
     * @see http://php.net/manual/en/function.date.php
     * @param null|int $storeId
     * @return string
     */
    public function getDateFormat(int $storeId = null): ?string
    {
        $format = $this->scopeConfig->getValue(
            self::XML_PATH_DATE_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($format == \MageWorx\DeliveryDate\Model\Source\DateFormat::CUSTOM_FORMAT_VALUE) {
            $format = $this->scopeConfig->getValue(
                self::XML_PATH_DATE_FORMAT_CUSTOM,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $format;
    }

    /**
     * Converts date from default format to the store specific format
     *
     * @param $date
     * @param null|int $storeId
     * @return string
     */
    public function formatDateFromDefaultToStoreSpecific($date, int $storeId = null): string
    {
        $storeDateFormat = $this->getDateFormat($storeId);
        if (!$date instanceof \DateTimeInterface) {
            $date       = (string)$date;
            $dateObject = \DateTime::createFromFormat('Y-m-d', $date);
            if ($dateObject === false) {
                $dateObject = new \DateTime($date);
            }
        } else {
            $dateObject = $date;
        }

        if (!$dateObject) {
            return $date;
        }

        $dateFormatted = $this->timezone->formatDateTime(
            $dateObject,
            null,
            null,
            $this->resolver->getLocale(),
            $dateObject->getTimezone()->getName(),
            $storeDateFormat
        );

        return $dateFormatted;
    }

    /**
     * Returns source model of the working days
     *
     * @return \MageWorx\DeliveryDate\Model\Source\WorkingDays
     */
    public function getWorkingDaysSourceModel(): \MageWorx\DeliveryDate\Model\Source\WorkingDays
    {
        return $this->workingDaysSourceModel;
    }

    /**
     * Is need to replace available dates to the words
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isNeedToReplaceDateToWords(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REPLACE_DATE_TO_WORDS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Default Delivery Option id
     *
     * @return int
     */
    public function getDefaultDeliveryOptionId(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_DELIVERY_OPTION_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is delivery date field on the checkout required
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDeliveryDateRequired(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DELIVERY_DATE_REQUIRED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is delivery date preselection on the checkout enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isPreSelectDeliveryDateEnabled(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRE_SELECT_DELIVERY_DATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $data
     * @param int $i
     * @return int|null
     */
    public function getPart($data, $i): ?int
    {
        if ($data && mb_stripos($data, QueueDataInterface::TIME_DELIMITER) !== null) {
            $parts = explode(QueueDataInterface::TIME_DELIMITER, $data);

            return isset($parts[$i]) ? (int)$parts[$i] : null;
        }

        return null;
    }

    /**
     * Get Delivery Date Title
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDeliveryDateTitle(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DELIVERY_DATE_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Delivery Time Title
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDeliveryTimeTitle(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DELIVERY_TIME_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param float $amount
     * @param bool $format
     * @return float|string
     */
    public function convertBaseAmountToStoreCurrencyAmount($amount, $format = false)
    {
        if ($format) {
            return $this->priceCurrency->convertAndFormat($amount, false);
        } else {
            return $this->priceCurrency->convert($amount);
        }
    }

    /**
     * FLag: use minimum delivery days or maximum
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getUseProductMinDeliveryDays(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_MIN_DELIVERY_DAYS_FOM_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Flag: is need to block delivery time indent by product EDT
     * when it is disabled on at least one product from current cart.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isBlockIfOnAnyProductDisabledEDT(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BLOCK_WHEN_DISABLED_ON_ANY_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get message format for the estimated delivery time on the product page
     * Variables: {{days_from_number}} , {{days_to_number}} , {{days_from_date}} , {{days_to_date}}
     *
     * @param int|null $storeId
     * @return string
     */
    public function getProductEDTMessageFormat(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EDT_MESSAGE_FORMAT_FOR_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get message fro same-day-delivery
     *
     * @param int|null $storeId
     * @return string
     */
    public function getProductSameDayDeliveryMessage($storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EDT_SAME_DAY_DELIVERY_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get message for next-day-delivery
     *
     * @param int|null $storeId
     * @return string
     */
    public function getProductNextDayDeliveryMessage($storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EDT_NEXT_DAY_DELIVERY_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Method which can be used to set use calendar days as offset (taken from product)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function useCalendarDaysOffset($storeId = null): bool
    {
        return false;
    }

    /**
     * Is need to display empty delivery date block in the order view page
     * (Ability to edit DD in orders without delivery date set by customer)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEmptyDeliveryDateBlockVisible(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_EMPTY_DD_BLOCK_IN_ADMIN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Determine is need to filter delivery date availability by product salable qty
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isUseStock(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_STOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Error message for products
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDeliveryDateProductErrorMessage(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isSelectProductOptionsMessageVisible(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_SELECT_OPTIONS_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSelectProductOptionsMessage(int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELECT_OPTIONS_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isErrorMessageVisibleOnProduct(int $storeId = null): bool
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_UNAVAILABLE_DELIVERY_ACTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) == \MageWorx\DeliveryDate\Model\Source\UnavailableDeliveryAction::DISPLAY_ERROR_MESSAGE;
    }

    /**
     * Is need to set first available delivery date for orders without date
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isNeedToSetDefaultDeliveryDate(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SET_DEFAULT_DELIVERY_DATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function includeNonWorkingDaysInProcessingOrderPeriod(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INCLUDE_NON_WORKING_DAYS_IN_PROCESSING_PERIOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function reloadShippingRatesAfterExtraChargeApplied(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_RELOAD_SHIPPING_RATES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getFirstDayIndex(int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            'general/locale/firstday',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * When enabled estimated delivery period TO will be used as a limit
     * for delivery date max calculation days
     *
     * @param int|null $storeId
     * @return bool
     */
    public function useEstimatedDeliveryPeriodToAsLimit(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_LIMIT_BY_ESTIMATED_DELIVERY_PERIOD_TO,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param DeliveryDateDataInterface $deliveryDateData
     * @return string
     */
    public function getTimeStingFromDeliveryDataObject(DeliveryDateDataInterface $deliveryDateData): string
    {
        $time = '';

        $hoursFrom = $deliveryDateData->getDeliveryHoursFrom();
        $time      .= $hoursFrom < 10 ? '0' . $hoursFrom : $hoursFrom;

        $time .= QueueDataInterface::TIME_DELIMITER;

        $minutesFrom = $deliveryDateData->getDeliveryMinutesFrom();
        $time        .= $minutesFrom < 10 ? '0' . $minutesFrom : $minutesFrom;

        $time .= '_';

        if (!$deliveryDateData->getDeliveryHoursTo() && !$deliveryDateData->getDeliveryMinutesTo()) {
            $time .= '24:00';
        } else {
            $hoursTo = $deliveryDateData->getDeliveryHoursTo();
            $time    .= $hoursTo < 10 ? '0' . $hoursTo : $hoursTo;

            $time .= QueueDataInterface::TIME_DELIMITER;

            $minutesTo = $deliveryDateData->getDeliveryMinutesTo();
            $time      .= $minutesTo < 10 ? '0' . $minutesTo : $minutesTo;
        }

        return $time;
    }
}
