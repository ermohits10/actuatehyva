<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model;

use Magento\Bundle\Model\Product\Type as BundleProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Shipping\Model\Config\Source\Allmethods as ShippingMethodsSourceModel;
use MageWorx\DeliveryDate\Api\Data\DeliveryOptionDataInterface;
use MageWorx\DeliveryDate\Api\Data\LimitsInterface;
use MageWorx\DeliveryDate\Api\DeliveryLimitsConverterInterface;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionConditionsInterface;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterface;
use MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterfaceFactory;
use MageWorx\DeliveryDate\Api\QuoteToConditionsConverterInterface;
use MageWorx\DeliveryDate\Exceptions\DeliveryTimeException;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use MageWorx\DeliveryDate\Model\ResourceModel\DeliveryOption\CollectionFactory as DeliveryOptionCollectionFactory;

class DeliveryManager implements DeliveryManagerInterface
{
    /**
     * @var ResourceModel\DeliveryOption\CollectionFactory
     */
    private $deliveryOptionCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShippingMethodsSourceModel
     */
    private $allShippingMethodsSource;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var array
     */
    private $deliveryOptionCache = [];

    /**
     * @var int
     */
    private $daysOffset = 0;

    /**
     * Is advanced data should present in the response?
     *
     * @var bool
     */
    private $advanced = false;

    /**
     * @var string
     */
    private $shippingMethodFilter = '';

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * How many days to calculate (maximum limit). Could be overwritten by product configuration.
     *
     * @var int
     */
    private $maxCalculationDays = 0;

    /**
     * @var QuoteMinMaxDateInterfaceFactory
     */
    private $quoteMinMaxDateFactory;

    /**
     * @var DeliveryLimitsConverterInterface
     */
    private $deliveryLimitsConverter;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var QuoteToConditionsConverterInterface
     */
    private $quoteToConditionsConverter;

    /**
     * @param DeliveryOptionCollectionFactory $deliveryOptionCollectionFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ShippingMethodsSourceModel $allShippingMethodsSource
     * @param Helper $helper
     * @param QuoteMinMaxDateInterfaceFactory $quoteMinMaxDateFactory
     * @param DeliveryLimitsConverterInterface $deliveryLimitsConverter
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param QuoteToConditionsConverterInterface $quoteToConditionsConverter
     * @param EventManager $eventManager
     */
    public function __construct(
        DeliveryOptionCollectionFactory     $deliveryOptionCollectionFactory,
        CartRepositoryInterface             $cartRepository,
        ShippingMethodsSourceModel          $allShippingMethodsSource,
        Helper                              $helper,
        QuoteMinMaxDateInterfaceFactory     $quoteMinMaxDateFactory,
        DeliveryLimitsConverterInterface    $deliveryLimitsConverter,
        MaskedQuoteIdToQuoteIdInterface     $maskedQuoteIdToQuoteId,
        QuoteToConditionsConverterInterface $quoteToConditionsConverter,
        EventManager                        $eventManager
    ) {
        $this->deliveryOptionCollectionFactory = $deliveryOptionCollectionFactory;
        $this->cartRepository                  = $cartRepository;
        $this->allShippingMethodsSource        = $allShippingMethodsSource;
        $this->helper                          = $helper;
        $this->quoteMinMaxDateFactory          = $quoteMinMaxDateFactory;
        $this->deliveryLimitsConverter         = $deliveryLimitsConverter;
        $this->maskedQuoteIdToQuoteId          = $maskedQuoteIdToQuoteId;
        $this->quoteToConditionsConverter      = $quoteToConditionsConverter;
        $this->eventManager                    = $eventManager;
    }

    /**
     * Set offset days
     *
     * @param int $days
     * @return $this
     */
    public function setDaysOffset($days = 0): DeliveryManagerInterface
    {
        $this->daysOffset = $days;

        return $this;
    }

    /**
     * Get current days offset
     *
     * @return int
     */
    public function getDaysOffset(): int
    {
        return (int)$this->daysOffset;
    }

    /**
     * @param string $method
     * @param string|\DateTimeInterface $date
     * @param int $customerGroupId
     * @param int $storeId
     * @param bool $asArray
     *
     * @return DeliveryOptionInterface|array
     * @throws LocalizedException
     * @api
     */
    public function getDeliveryOptionForMethod(
        string $method,
               $date,
        int    $customerGroupId,
        int    $storeId,
        bool   $asArray = false
    ) {
        \Magento\Framework\Profiler::start('get_delivery_option_for_method_' . $method);

        $cacheKey = hash('sha256', json_encode([$method, $date, $customerGroupId, $storeId, $asArray]));
        if (!empty($this->deliveryOptionCache[$cacheKey])) {
            return $this->deliveryOptionCache[$cacheKey];
        }

        /** @var ResourceModel\DeliveryOption\Collection $deliveryOptionCollection */
        $deliveryOptionCollection = $this->deliveryOptionCollectionFactory->create();
        $deliveryOptionCollection->addFilter(DeliveryOptionDataInterface::KEY_IS_ACTIVE, 1)
                                 ->addShippingMethodFilter($method)
                                 ->addDateFilter($date)
                                 ->addCustomerGroupFilter($customerGroupId)
                                 ->addStoreFilter($storeId)
                                 ->setOrder(
                                     DeliveryOptionDataInterface::KEY_SORT_ORDER,
                                     DataCollection::SORT_ORDER_ASC
                                 )
                                 ->setPageSize(1)
                                 ->setCurPage(1);

        /** @var DeliveryOptionInterface|DeliveryOption $deliveryOption */
        $deliveryOption = $deliveryOptionCollection->getFirstItem();
        if ($deliveryOption->getId()) {
            $deliveryOption->setNonWorkingDaysByCart($this->getNonWorkingDaysFromProductsInCart());

            /** @var \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface $quoteMinMaxDate */
            $quoteMinMaxDate = $this->quoteMinMaxDateFactory->create(['quote' => $this->getQuote()]);
            /** @var \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface $dateDiapason */
            $dateDiapason = $quoteMinMaxDate->getDiapason();

            $deliveryOption->setStartDate($dateDiapason->getMinDate());
            $deliveryOption->setEndDate($dateDiapason->getMaxDate());

            if ($this->getIsAdvanced()) {
                $deliveryOption->fillDayLimitsWithDisabled(
                    $date,
                    $this->getDaysOffset(),
                    $this->getMaxCalculationDays()
                );
            } else {
                $deliveryOption->fillDayLimits(
                    $date,
                    $this->getDaysOffset(),
                    $this->getMaxCalculationDays()
                );
            }
        }

        $deliveryOption->setErrorMessage($deliveryOption->getDeliveryDateRequiredErrorMessage());

        $this->deliveryOptionCache[$cacheKey] = $deliveryOption;

        if ($asArray) {
            $data = $deliveryOption->getData();
            \Magento\Framework\Profiler::stop('get_delivery_option_for_method_' . $method);

            return $data;
        }

        \Magento\Framework\Profiler::stop('get_delivery_option_for_method_' . $method);

        return $deliveryOption;
    }

    /**
     * @param DeliveryOptionConditionsInterface $deliveryOptionConditions
     * @param bool $advanced
     * @return \MageWorx\DeliveryDate\Api\Data\LimitsInterface[]
     * @throws LocalizedException
     * @api
     */
    public function getDeliveryOptionLimitsByConditions(
        DeliveryOptionConditionsInterface $deliveryOptionConditions,
        bool                              $advanced = false
    ): array {
        $limitsArray = [];

        $deliveryOption = $this->getDeliveryOptionByConditions($deliveryOptionConditions, $advanced);

        if ($deliveryOption) {
            $limitsArray['any'] = $deliveryOption;
        }

        // Converting to a Data Objects because API does not support the array return type.
        return $this->deliveryLimitsConverter->convertToObjectArray($limitsArray);
    }

    /**
     * @param DeliveryOptionConditionsInterface $deliveryOptionConditions
     * @param bool|null $advanced
     * @return DeliveryOptionInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDeliveryOptionByConditions(
        DeliveryOptionConditionsInterface $deliveryOptionConditions,
        ?bool                             $advanced = false
    ): ?DeliveryOptionInterface {
        $this->setIsAdvanced($advanced);
        $quote = $deliveryOptionConditions->getQuote();
        $this->setQuote($quote);

        // Prepare conditions
        if (!$deliveryOptionConditions->getStoreId()) {
            $deliveryOptionConditions->setStoreId((int)$quote->getStoreId());
        }

        if (!$deliveryOptionConditions->getCustomerGroupId()) {
            $deliveryOptionConditions->setCustomerGroupId((int)$quote->getCustomerGroupId());
        }

        if ($this->deliveryTimeDisabledOnAllProducts($quote)) {
            return null;
        }

        $this->eventManager->dispatch(
            'mageworx_delivery_date_get_available_limits_for_quote_before',
            ['delivery_date_manager' => $this, 'quote' => $quote]
        );

        // Calculate start and end day
        $date       = $this->calculateStartDate($quote);
        $daysOffset = $this->calculateDaysOffset($quote);
        if ($this->getDaysOffset() < $daysOffset) {
            $this->setDaysOffset($daysOffset);
        }

        /** @var ResourceModel\DeliveryOption\Collection $deliveryOptionCollection */
        $deliveryOptionCollection = $this->deliveryOptionCollectionFactory->create();
        $deliveryOptionCollection->addFieldToFilter(DeliveryOptionDataInterface::KEY_IS_ACTIVE, 1)
                                 ->addDateFilter($date)
                                 ->addFilterByConditions($deliveryOptionConditions)
                                 ->setOrder(
                                     DeliveryOptionDataInterface::KEY_SORT_ORDER,
                                     DataCollection::SORT_ORDER_ASC
                                 );

        /** @var DeliveryOptionInterface|DeliveryOption $deliveryOption */
        $deliveryOption = $deliveryOptionCollection->getSuitableDeliveryOptionByConditions($deliveryOptionConditions);

        if ($deliveryOption && $deliveryOption->getId()) {
            $deliveryOption->setNonWorkingDaysByCart($this->getNonWorkingDaysFromProductsInCart());

            /** @var \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface $quoteMinMaxDate */
            $quoteMinMaxDate = $this->quoteMinMaxDateFactory->create(['quote' => $this->getQuote()]);
            /** @var \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface $dateDiapason */
            $dateDiapason = $quoteMinMaxDate->getDiapason();

            $deliveryOption->setStartDate($dateDiapason->getMinDate());
            $deliveryOption->setEndDate($dateDiapason->getMaxDate());

            if ($this->getIsAdvanced()) {
                $deliveryOption->fillDayLimitsWithDisabled(
                    $date,
                    $this->getDaysOffset(),
                    $this->getMaxCalculationDays()
                );
            } else {
                $deliveryOption->fillDayLimits(
                    $date,
                    $this->getDaysOffset(),
                    $this->getMaxCalculationDays()
                );
            }

            $deliveryOption->setErrorMessage($deliveryOption->getDeliveryDateRequiredErrorMessage());
        }

        return $deliveryOption;
    }

    /**
     * Get Delivery Option suitable for all shipping methods
     *
     * @param \DateTimeInterface|null $date
     * @param int|null $customerGroupId
     * @param int $storeId
     * @param bool $asArray
     * @return DeliveryOptionInterface
     * @throws LocalizedException
     */
    public function getDeliveryOptionForAllMethods(
        $date = null,
        ?int $customerGroupId = null,
        $storeId = 0,
        $asArray = false
    ) {
        \Magento\Framework\Profiler::start('get_default_delivery_option_for_all_methods');

        $cacheKey = hash('sha256', json_encode([$date, $customerGroupId, $storeId, $asArray, $this->getIsAdvanced()]));
        if (!empty($this->deliveryOptionCache[$cacheKey])) {
            return $this->deliveryOptionCache[$cacheKey];
        }

        $date = $this->convertAnyTypeOfDateToObject($date);

        /** @var ResourceModel\DeliveryOption\Collection $deliveryOptionCollection */
        $deliveryOptionCollection = $this->deliveryOptionCollectionFactory->create();
        if ($customerGroupId !== null) {
            $deliveryOptionCollection->addCustomerGroupFilter($customerGroupId);
        }

        if (!is_array($storeId)) {
            $storeId = [$storeId];
        }

        if ($this->getQuote()) {
            $deliveryOptionCondition = $this->convertQuoteToConditions($this->getQuote());
        }

        $deliveryOptionCollection->addStoreFilter($storeId)
                                 ->addFilter(DeliveryOptionDataInterface::KEY_IS_ACTIVE, 1)
                                 ->addDateFilter($date)
                                 ->addFieldToFilter(
                                     DeliveryOptionDataInterface::KEY_SHIPPING_METHODS_CHOICE_LIMITER,
                                     [
                                         'eq' => DeliveryOptionDataInterface::SHIPPING_METHODS_CHOICE_LIMIT_ALL_METHODS
                                     ]
                                 )
                                 ->setOrder(
                                     DeliveryOptionDataInterface::KEY_SORT_ORDER,
                                     DataCollection::SORT_ORDER_ASC
                                 );

        /** @var DeliveryOptionInterface $deliveryOption */
        if (isset($deliveryOptionCondition)) {
            $deliveryOption = $deliveryOptionCollection->getSuitableDeliveryOptionByConditions(
                $deliveryOptionCondition
            );
        } else {
            $deliveryOption = $deliveryOptionCollection->getFirstItem();
        }

        if ($deliveryOption && $deliveryOption->getId()) {
            $deliveryOption->setNonWorkingDaysByCart($this->getNonWorkingDaysFromProductsInCart());

            /** @var \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface $quoteMinMaxDate */
            $quoteMinMaxDate = $this->quoteMinMaxDateFactory->create(['quote' => $this->getQuote()]);
            /** @var \MageWorx\DeliveryDate\Api\Data\DateDiapasonInterface $dateDiapason */
            $dateDiapason = $quoteMinMaxDate->getDiapason();

            $deliveryOption->setStartDate($dateDiapason->getMinDate());
            $deliveryOption->setEndDate($dateDiapason->getMaxDate());

            if ($this->getIsAdvanced()) {
                $deliveryOption->fillDayLimitsWithDisabled(
                    $date,
                    $this->getDaysOffset(),
                    $this->getMaxCalculationDays()
                );
            } else {
                $deliveryOption->fillDayLimits(
                    $date,
                    $this->getDaysOffset(),
                    $this->getMaxCalculationDays()
                );
            }

            $deliveryOption->setErrorMessage($deliveryOption->getDeliveryDateRequiredErrorMessage());

            $this->deliveryOptionCache[$cacheKey] = $deliveryOption;
        }

        if ($asArray) {
            $data = $deliveryOption ? $deliveryOption->getData() : [];
            \Magento\Framework\Profiler::stop('get_default_delivery_option_for_all_methods');

            return $data;
        }

        \Magento\Framework\Profiler::stop('get_default_delivery_option_for_all_methods');

        return $deliveryOption;
    }

    /**
     * Find shipping method codes for which Delivery Option is available
     *
     * @param null|\DateTimeInterface|string $date
     * @param null|int $customerGroupId
     * @param null|int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getAvailableForMethods($date = null, ?int $customerGroupId = null, $storeId = 0): array
    {
        \Magento\Framework\Profiler::start('get_delivery_options_available_for_methods');

        $date = $this->convertAnyTypeOfDateToObject($date);

        /** @var ResourceModel\DeliveryOption\Collection $deliveryOptionCollection */
        $deliveryOptionCollection = $this->deliveryOptionCollectionFactory->create();
        if ($customerGroupId !== null) {
            $deliveryOptionCollection->addCustomerGroupFilter($customerGroupId);
        }

        if (!is_array($storeId)) {
            $storeId = [$storeId];
        }

        $deliveryOptionCollection->addStoreFilter($storeId)
                                 ->addFilter(DeliveryOptionDataInterface::KEY_IS_ACTIVE, 1)
                                 ->addDateFilter($date)
                                 ->addFieldToSelect(DeliveryOptionInterface::KEY_METHODS)
                                 ->addFieldToFilter(
                                     DeliveryOptionDataInterface::KEY_SHIPPING_METHODS_CHOICE_LIMITER,
                                     [
                                         'neq' => DeliveryOptionDataInterface::SHIPPING_METHODS_CHOICE_LIMIT_ALL_METHODS
                                     ]
                                 );
        $result = $deliveryOptionCollection->getConnection()
                                           ->fetchAll($deliveryOptionCollection->getSelect());

        $methods = [];
        foreach ($result as $data) {
            $availableMethods = empty($data[DeliveryOptionInterface::KEY_METHODS]) ?
                [] :
                explode(',', $data[DeliveryOptionInterface::KEY_METHODS]);
            if (!$availableMethods) {
                return [];
            }
            $methods = array_merge($methods, $availableMethods);
        }
        $methods = array_unique($methods);

        \Magento\Framework\Profiler::stop('get_delivery_options_available_for_methods');

        return $methods;
    }

    /**
     * Get available limits for the quote by its id
     *
     * @param int $quoteId
     * @param bool $advanced
     * @return LimitsInterface[]
     * @throws LocalizedException
     */
    public function getAvailableLimitsForQuoteById(int $quoteId, bool $advanced = false): array
    {
        $this->setIsAdvanced($advanced);
        $result = [];

        try {
            /** @var CartInterface|Quote $quote */
            $quote = $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $exception) {
            return $result;
        }

        $limitsArray = $this->getAvailableLimitsForQuote($quote, $advanced);

        // Converting to a Data Objects because API does not support the array return type.
        return $this->deliveryLimitsConverter->convertToObjectArray($limitsArray);
    }

    /**
     * @param string $cartId
     * @param bool $advanced - is need to return "unavailable" (reserved) dates
     * @return \MageWorx\DeliveryDate\Api\Data\LimitsInterface[]
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getAvailableDeliveryDatesForGuestCart(string $cartId, bool $advanced = false): array
    {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($cartId);

        return $this->getAvailableLimitsForQuoteById($cartId, $advanced);
    }

    /**
     * Get available limits for the quote (object)
     *
     * @param CartInterface|Quote $quote
     * @param bool $advanced
     * @return array
     * @throws LocalizedException
     */
    public function getAvailableLimitsForQuote(CartInterface $quote, bool $advanced = false): array
    {
        $this->setIsAdvanced($advanced);
        $this->setQuote($quote);

        $storeId         = $quote->getStoreId();
        $customerGroupId = (int)$quote->getCustomerGroupId();

        if ($this->deliveryTimeDisabledOnAllProducts($quote)) {
            return [];
        }

        $this->eventManager->dispatch(
            'mageworx_delivery_date_get_available_limits_for_quote_before',
            ['delivery_date_manager' => $this, 'quote' => $quote]
        );

        // Calculate start and end day
        $date       = $this->calculateStartDate($quote);
        $daysOffset = $this->calculateDaysOffset($quote);
        if ($this->getDaysOffset() < $daysOffset) {
            $this->setDaysOffset($daysOffset);
        }

        \Magento\Framework\Profiler::start('get_all_shipping_methods');
        $shippingMethodsAvailable = $this->allShippingMethodsSource->toOptionArray(true);
        \Magento\Framework\Profiler::stop('get_all_shipping_methods');

        // Shipping Methods for which specific delivery option is available
        $deliveryOptionsAvailableForMethods = $this->getAvailableForMethods(
            $date,
            $customerGroupId,
            $storeId
        );

        // Default delivery option for all another methods
        $defaultDeliveryOption = $this->getDeliveryOptionForAllMethods(
            $date,
            $customerGroupId,
            $storeId,
            true
        );

        $deliveryDatesByMethod = [];

        foreach ($shippingMethodsAvailable as $carrierData) {
            $methods = $carrierData['value'];
            if (empty($methods)) {
                continue;
            }

            foreach ($methods as $methodsData) {
                if (empty($methodsData['value'])) {
                    continue;
                }

                $method = $methodsData['value'];

                // Filter delivery option by specific shipping method code
                if ($this->getShippingMethodFilter() && $this->getShippingMethodFilter() !== $method) {
                    continue;
                }

                // Restricts empty methods and methods for which we have no delivery option
                // (prevents unnecessary query to the db in a loop)
                if ($defaultDeliveryOption &&
                    !in_array($method, $deliveryOptionsAvailableForMethods)
                ) {
                    $deliveryDatesByMethod[$method] = $defaultDeliveryOption;
                } else {
                    $additionalData                 = [
                        'shipping_method' => $method
                    ];
                    $condition                      = $this->convertQuoteToConditions($quote, $additionalData);
                    $deliveryDatesByMethod[$method] =
                        $this->getDeliveryOptionByConditions(
                            $condition,
                            $advanced
                        );
                }

                $deliveryDatesByMethod[$method]['method'] = $method;
            }
        }

        if (!empty($deliveryDatesByMethod['instore_instore'])) {
            $deliveryDatesByMethod['instore_pickup'] = $deliveryDatesByMethod['instore_instore'];
        }

        $this->eventManager->dispatch(
            'mageworx_delivery_date_get_available_limits_for_quote_after',
            ['delivery_date_manager' => $this, 'quote' => $quote, 'delivery_dates' => $deliveryDatesByMethod]
        );

        return $deliveryDatesByMethod;
    }

    /**
     * @param CartInterface|Quote $quote
     * @return \DateTime
     * @throws \Exception
     */
    private function calculateStartDate(CartInterface $quote): \DateTimeInterface
    {
        $extraDays = 0;
        $date      = new \DateTime(sprintf('+%d days', $extraDays));

        return $date;
    }

    /**
     * @param CartInterface|Quote $quote
     * @return int
     * @throws \Exception
     */
    public function calculateDaysOffset(CartInterface $quote): int
    {
        $extraDays = 0;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $item->getProduct();
            if ($product->getTypeId() === ConfigurableProduct::TYPE_CODE) {
                $children = $item->getChildren();
                if (!empty($children) && is_array($children)) {
                    $childQuoteItem = current($children);
                    $product        = $childQuoteItem->getProduct();
                }
            }
            /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
            $resource = $product->getResource();

            $isEnabled = (bool)$resource->getAttributeRawValue(
                $product->getId(),
                'mw_delivery_time_enabled',
                $item->getStoreId()
            );

            $minDays = (int)$resource->getAttributeRawValue(
                $product->getId(),
                'mw_delivery_time_from',
                $item->getStoreId()
            );
            $maxDays = (int)$resource->getAttributeRawValue(
                $product->getId(),
                'mw_delivery_time_to',
                $item->getStoreId()
            );

            if ($isEnabled) {
                if ($this->helper->getUseProductMinDeliveryDays()) {
                    $productExtraDays = $minDays;
                } else {
                    $productExtraDays = $maxDays;
                }

                /**
                 * Update max calculation days in case it is not set (0) or if product has fewer days in configuration
                 * and this feature is enabled in the store configuration
                 * ("Use the 'To' Product Estimated Delivery Period as limit for max calculation days").
                 */
                if (($this->getMaxCalculationDays() === 0 || $this->getMaxCalculationDays() > $maxDays)
                    && $this->helper->useEstimatedDeliveryPeriodToAsLimit()
                ) {
                    $this->setMaxCalculationDays($maxDays);
                }
                $extraDays = max($extraDays, $productExtraDays);
            } elseif ($this->helper->isBlockIfOnAnyProductDisabledEDT()) {
                throw new DeliveryTimeException();
            }
        }

        return $extraDays;
    }

    /**
     * Set max calculation days.
     * Usually based on product configuration.
     *
     * @param int $days
     */
    public function setMaxCalculationDays(int $days): void
    {
        $this->maxCalculationDays = $days;
    }

    /**
     * Get max calculation days.
     * Usually based on product configuration.
     *
     * @return int
     */
    public function getMaxCalculationDays(): int
    {
        return (int)$this->maxCalculationDays;
    }

    /**
     * Get flag by which advanced data could be added to the response or not
     *
     * @return bool
     */
    public function getIsAdvanced(): bool
    {
        return (bool)$this->advanced;
    }

    /**
     * Set flag which adds advanced data to the response
     *
     * @param bool $value
     * @return DeliveryManagerInterface
     */
    public function setIsAdvanced(bool $value): DeliveryManagerInterface
    {
        $this->advanced = $value;

        return $this;
    }

    /**
     * Non working days selected in he products from the current cart
     *
     * @return array|string[]
     */
    public function getNonWorkingDaysFromProductsInCart(): array
    {
        $nonWorkingDays = [];
        $quote          = $this->getQuote();
        if (!$quote) {
            return $nonWorkingDays;
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $item->getProduct();
            if ($product->getTypeId() === ConfigurableProduct::TYPE_CODE) {
                $children = $item->getChildren();
                if (!empty($children) && is_array($children)) {
                    $childQuoteItem = current($children);
                    $product        = $childQuoteItem->getProduct();
                }
            }

            /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
            $resource = $product->getResource();

            $productNonWorkingDays = $resource->getAttributeRawValue(
                $product->getId(),
                'mw_non_working_days',
                $item->getStoreId()
            );

            if (!is_array($productNonWorkingDays)) {
                $productNonWorkingDays = explode(',', (string)$productNonWorkingDays);
            }

            $productNonWorkingDays = array_filter($productNonWorkingDays);
            $nonWorkingDays        = array_merge($nonWorkingDays, $productNonWorkingDays);
        }

        $nonWorkingDays = array_unique($nonWorkingDays);

        return $nonWorkingDays;
    }

    /**
     * Set actual quote
     *
     * @param CartInterface $quote
     * @return $this
     */
    public function setQuote(CartInterface $quote): DeliveryManagerInterface
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get actual quote
     *
     * @return CartInterface|Quote|null
     */
    public function getQuote(): ?CartInterface
    {
        return $this->quote;
    }

    /**
     * Check is all products has disabled setting "Allow Delivery Date"
     *
     * @param CartInterface|Quote $quote
     * @return bool
     */
    public function deliveryTimeDisabledOnAllProducts(CartInterface $quote): bool
    {
        // Allow for empty quote
        if ($quote->getItemsQty() <= 0) {
            return false;
        }

        /** @var \Magento\Catalog\Model\Product[] $products */
        $products = [];
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($product->getTypeId() === ConfigurableProduct::TYPE_CODE) {
                $children = $item->getChildren();
                if (!empty($children) && is_array($children)) {
                    $childQuoteItem = current($children);
                    $products[]     = $childQuoteItem->getProduct();
                }
            } elseif ($product->getTypeId() === BundleProduct::TYPE_CODE) {
                $children = $item->getChildren();
                foreach ($children as $childQuoteItem) {
                    $products[] = $childQuoteItem->getProduct();
                }
            } else {
                $products[] = $item->getProduct();
            }
        }

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
            $resource = $product->getResource();

            $isEnabled = $resource->getAttributeRawValue(
                $product->getId(),
                'mw_delivery_time_enabled',
                $item->getStoreId()
            );

            if ($isEnabled || $isEnabled === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * The code of shipping method by which we should filter result of delivery dates calculations.
     *
     * @return string
     */
    public function getShippingMethodFilter(): string
    {
        return $this->shippingMethodFilter;
    }

    /**
     * The code of shipping method by which we should filter result of delivery dates calculations.
     *
     * @param string $value
     * @return DeliveryManagerInterface
     */
    public function setShippingMethodFilter(string $value): \MageWorx\DeliveryDate\Api\DeliveryManagerInterface
    {
        $this->shippingMethodFilter = $value;

        return $this;
    }

    /**
     * Convert regular quote to conditions
     *
     * @param CartInterface|Quote $quote
     * @param array|null $additionalData
     * @return DeliveryOptionConditionsInterface
     */
    public function convertQuoteToConditions(
        CartInterface $quote,
        ?array        $additionalData = []
    ): DeliveryOptionConditionsInterface {
        /** @var DeliveryOptionConditionsInterface $conditions */
        $conditions = $this->quoteToConditionsConverter->convert($quote, $additionalData);

        return $conditions;
    }

    /**
     * @param mixed $date
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public function convertAnyTypeOfDateToObject($date): \DateTimeInterface
    {
        if (!$date) {
            $date = new \DateTime();
        }

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        if (!$date instanceof \DateTimeInterface) {
            throw new LocalizedException(__('Date must be of type DateTimeInterface'));
        }

        return $date;
    }
}
