<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Api\Data;

/**
 * Interface DeliveryOptionData
 *
 * Delivery option entity
 *
 */
interface DeliveryOptionDataInterface
{
    const KEY_NAME                           = 'name';
    const KEY_METHODS                        = 'methods';
    const KEY_SORT_ORDER                     = 'sort_order';
    const KEY_ID                             = 'entity_id';
    const KEY_CUSTOMER_GROUPS                = 'customer_group_ids';
    const KEY_STORE_IDS                      = 'store_ids';
    const KEY_IS_ACTIVE                      = 'is_active';
    const KEY_ACTIVE_FROM                    = 'active_from';
    const KEY_ACTIVE_TO                      = 'active_to';
    const KEY_FUTURE_DAYS_LIMITS             = 'future_days_limit';
    const KEY_START_DAYS_LIMITS              = 'start_days_limit';
    const KEY_LIMITS_SERIALIZED              = 'limits_serialized';
    const KEY_HOLIDAYS_SERIALIZED            = 'holidays_serialized';
    const KEY_WORKING_DAYS                   = 'working_days';
    const KEY_DISABLE_SAME_DAY_DELIVERY_TIME = 'disable_same_day_delivery_time';
    const KEY_CUT_OFF_TIME                   = 'cut_off_time';
    const KEY_QUOTES_SCOPE                   = 'quotes_scope';

    const KEY_DELIVERY_DATE_REQUIRED_ERROR_MESSAGE = 'delivery_date_required_error_message';
    const KEY_DISABLE_DELIVERY_DATE_SELECTION      = 'disable_selection';
    const KEY_SHIPPING_METHODS_CHOICE_LIMITER      = 'shipping_methods_choice_limiter';

    const KEY_HOLIDAYS              = 'holidays';
    const KEY_DAY_LIMITS            = 'day_limits';
    const KEY_CONDITIONS_SERIALIZED = 'conditions_serialized';

    const TABLE_NAME             = 'mageworx_dd_delivery_option';
    const STORE_GROUP_TABLE_NAME = 'mageworx_dd_delivery_option_store_group';

    const SHIPPING_METHODS_CHOICE_LIMIT_ALL_METHODS      = 0;
    const SHIPPING_METHODS_CHOICE_LIMIT_SPECIFIC_METHODS = 1;

    const QUOTES_SCOPE_UNLIMITED       = '0';
    const QUOTES_SCOPE_PER_DAY         = '1';
    const QUOTES_SCOPE_PER_DAY_OF_WEEK = '2';

    const QUOTES_UNLIMITED = INF;

    const LIMITS_GENERAL = 'default';

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setName($value);

    /**
     * Returns method code for which this limit is available
     *
     * @return array
     */
    public function getMethods();

    /**
     * @param array $methods
     * @return DeliveryOptionDataInterface
     */
    public function setMethods(array $methods);

    /**
     * Returns sort order of the delivery option
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return DeliveryOptionDataInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Returns date and time from witch this delivery option was active
     *
     * @return \DateTimeInterface
     */
    public function getActiveFrom();

    /**
     * @param \DateTimeInterface $from
     * @return DeliveryOptionDataInterface
     */
    public function setActiveFrom(\DateTimeInterface $from);

    /**
     * Returns date and time to witch this delivery option was active
     *
     * @return \DateTimeInterface
     */
    public function getActiveTo();

    /**
     * @param \DateTimeInterface $to
     * @return DeliveryOptionDataInterface
     */
    public function setActiveTo(\DateTimeInterface $to);

    /**
     * Returns array of customer groups for witch this delivery option was active
     *
     * @return int[]
     */
    public function getCustomerGroups();

    /**
     * @param int[] $groupIds
     * @return DeliveryOptionDataInterface
     */
    public function setCustomerGroups($groupIds);

    /**
     * Returns array of store ids for witch this delivery option was active
     *
     * @return string[]
     */
    public function getStoreIds();

    /**
     * @param string[] $storeIds
     * @return DeliveryOptionDataInterface
     */
    public function setStoreIds($storeIds);

    /**
     * Delivery Option Id
     *
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return DeliveryOptionDataInterface
     */
    public function setId($id);

    /**
     * Is Active?
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * @param bool $flag
     * @return DeliveryOptionDataInterface
     */
    public function setIsActive($flag);

    /**
     * Returns count of the days for which limits rendered during checkout
     *
     * @return int
     */
    public function getFutureDaysLimit();

    /**
     * @param int $value
     * @return DeliveryOptionDataInterface
     */
    public function setFutureDaysLimit($value);

    /**
     * Returns first day from which limits rendered during checkout (first available day from the today)
     *
     * @return int
     */
    public function getStartDaysLimit();

    /**
     * @param int $value
     * @return DeliveryOptionDataInterface
     */
    public function setStartDaysLimit($value);

    /**
     * @return array
     */
    public function getLimitsSerialized();

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setLimitsSerialized($value);

    /**
     * @return array
     */
    public function getHolidays();

    /**
     * @param array $value
     * @return DeliveryOptionDataInterface
     */
    public function setHolidays($value);

    /**
     * @return string
     */
    public function getHolidaysSerialized();

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setHolidaysSerialized($value);

    /**
     * @return int
     * @see \MageWorx\DeliveryDate\Model\Source\ShippingMethodsChoiceLimiter
     */
    public function getShippingMethodsChoiceLimiter(): int;

    /**
     * @param int $value
     * @return DeliveryOptionDataInterface
     */
    public function setShippingMethodsChoiceLimiter(int $value): DeliveryOptionDataInterface;

    /**
     * Get working days for which delivery option is available
     *
     * @return null|array
     */
    public function getWorkingDays();

    /**
     * Set working days for which delivery option is available
     *
     * @param array|string|null $value
     * @return DeliveryOptionDataInterface
     */
    public function setWorkingDays($value);

    /**
     * Get time after which the nearest day delivery should be disabled
     *
     * @return string
     */
    public function getCutOffTime();

    /**
     * Set time after which the nearest day delivery should be disabled
     *
     * @param string|null $value
     * @return DeliveryOptionDataInterface
     */
    public function setCutOffTime($value);

    /**
     * Get quotes scope
     *
     * @return int
     * @see \MageWorx\DeliveryDate\Model\Source\QuotesScope
     *
     */
    public function getQuotesScope();

    /**
     * Set quotes scope
     *
     * @param int $value
     * @return DeliveryOptionDataInterface
     * @see \MageWorx\DeliveryDate\Model\Source\QuotesScope
     *
     */
    public function setQuotesScope($value);

    /**
     * Set error message for case: delivery date is required
     *
     * @param string[] $messages
     * @return DeliveryOptionDataInterface
     */
    public function setDeliveryDateRequiredErrorMessages(array $messages): DeliveryOptionDataInterface;

    /**
     * Get error messages for case: delivery date is required (per store)
     *
     * @return array|null
     */
    public function getDeliveryDateRequiredErrorMessages(): ?array;

    /**
     * Get error messages for case: delivery date is required.
     * For specified or actual store.
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getDeliveryDateRequiredErrorMessage(int $storeId = null): ?string;

    /**
     * @param bool $flag
     * @return DeliveryOptionDataInterface
     */
    public function setDisableSelection(bool $flag): DeliveryOptionDataInterface;

    /**
     * @return bool
     */
    public function getDisableSelection(): bool;

    /**
     * @param string $value
     * @return DeliveryOptionDataInterface
     */
    public function setConditionsSerialized(string $value): DeliveryOptionDataInterface;

    /**
     * @return array
     */
    public function getConditionsSerialized(): array;
}
