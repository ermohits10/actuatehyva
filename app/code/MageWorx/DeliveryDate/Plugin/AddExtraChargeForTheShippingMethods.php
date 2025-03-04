<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Plugin;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use MageWorx\DeliveryDate\Api\DefaultDeliveryDateManagerInterface;
use MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;

class AddExtraChargeForTheShippingMethods
{
    /**
     * @var DeliveryOptionRepositoryInterface
     */
    private $deliveryOptionRepository;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var DefaultDeliveryDateManagerInterface
     */
    private $defaultDeliveryDateManager;

    /**
     * @param DeliveryOptionRepositoryInterface $deliveryOptionRepository
     * @param DefaultDeliveryDateManagerInterface $defaultDeliveryDateManager
     * @param Helper $helper
     */
    public function __construct(
        DeliveryOptionRepositoryInterface $deliveryOptionRepository,
        DefaultDeliveryDateManagerInterface $defaultDeliveryDateManager,
        Helper $helper
    ) {
        $this->deliveryOptionRepository   = $deliveryOptionRepository;
        $this->defaultDeliveryDateManager = $defaultDeliveryDateManager;
        $this->helper                     = $helper;
    }

    /**
     * Adds extra charge by selected delivery date
     *
     * @param CarrierInterface $subject
     * @param Result $result
     * @param RateRequest $request
     * @return Result
     * @throws \Exception
     */
    public function afterCollectRates(CarrierInterface $subject, $result, RateRequest $request)
    {
        if (!$result instanceof Result) {
            return $result;
        }

        try {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->getQuoteFromRequest($request);
            if (!$quote) {
                return $result;
            }
        } catch (NoSuchEntityException $noSuchEntityException) {
            return $result;
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress) {
            return $result;
        }

        // Trying to set default delivery date if enabled and shipping method has been selected
        $this->defaultDeliveryDateManager->addToQuote($quote);

        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        if (!$shippingAddressExtensionAttributes) {
            return $result;
        }

        $deliveryOptionId = (int)$shippingAddressExtensionAttributes->getDeliveryOptionId();
        if (!$deliveryOptionId) {
            return $result;
        }

        $extraCharge = 0;

        try {
            $deliveryOption = $this->deliveryOptionRepository->getById($deliveryOptionId);
        } catch (NoSuchEntityException $e) {
            return $result;
        } catch (LocalizedException $e) {
            return $result;
        }

        if (!$deliveryOption->isApplicableForShippingMethod((string)$shippingAddress->getShippingMethod())) {
            return $result;
        }

        $deliveryDate = new \DateTime((string)$shippingAddressExtensionAttributes->getDeliveryDay());
        $limits       = $deliveryOption->getLimit($deliveryDate);
        if (!empty($limits['extra_charge'])) {
            $extraCharge = (float)$limits['extra_charge'];
        }

        if (!empty($limits['time_limits'])) {
            try {
                $suitableTimeLimit = $this->detectSuitableTimeLimit(
                    $shippingAddressExtensionAttributes,
                    $limits['time_limits']
                );
                if (!empty($suitableTimeLimit['extra_charge'])) {
                    $extraCharge = (float)$suitableTimeLimit['extra_charge'];
                }
            } catch (LocalizedException $e) {
                // No extra charge from time limit
            }
        }

        foreach ($result->getAllRates() as $rate) {
            $methodCode = $this->getMethodCodeByRate($rate);
            if ($methodCode === $shippingAddress->getShippingMethod()) {
                $rate->setPrice($rate->getPrice() + $extraCharge);
            }
        }

        return $result;
    }

    /**
     * @param RateRequest $request
     * @return Quote
     * @throws NoSuchEntityException
     */
    private function getQuoteFromRequest(RateRequest $request): Quote
    {
        $items = $request->getAllItems();
        if (empty($items) || !is_array($items)) {
            throw new NoSuchEntityException(__('There was no items in request - unable to locate quote.'));
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = reset($items);
        $quote = $item->getQuote();

        return $quote;
    }

    /**
     * @param ExtensionAttributesInterface|AddressExtensionInterface $shippingAddressExtensionAttributes
     * @param array $timeLimits
     * @return array
     * @throws LocalizedException
     */
    private function detectSuitableTimeLimit(
        ExtensionAttributesInterface $shippingAddressExtensionAttributes,
        array $timeLimits
    ): array {
        $fullyCompatibleTime     = null;
        $selectedTime            = (string)$shippingAddressExtensionAttributes->getDeliveryTime();
        $selectedTimeParts       = explode('_', $selectedTime);
        $selectedTimeFrom        = isset($selectedTimeParts[0]) ? $selectedTimeParts[0] : null;
        $selectedTimeTo          = isset($selectedTimeParts[1]) ? $selectedTimeParts[1] : null;
        $timeLimitTemplate       = [
            'from' => $selectedTimeFrom,
            'to'   => $selectedTimeTo
        ];
        $selectedTimePartsParsed = $this->helper->parseFromToPartsFromTimeLimitTemplate($timeLimitTemplate);

        foreach ($timeLimits as $index => $timeLimit) {
            $fromToParts = $this->helper->parseFromToPartsFromTimeLimitTemplate($timeLimit);

            if ($fromToParts['from']['hours'] == $selectedTimePartsParsed['from']['hours'] &&
                $fromToParts['from']['minutes'] == $selectedTimePartsParsed['from']['minutes'] &&
                $fromToParts['to']['hours'] == $selectedTimePartsParsed['to']['hours'] &&
                $fromToParts['to']['minutes'] == $selectedTimePartsParsed['to']['minutes']
            ) {
                $fullyCompatibleTime = $timeLimit;
                break;
            }

            if ($fromToParts['from']['hours'] >= $selectedTimePartsParsed['from']['hours'] &&
                $fromToParts['from']['minutes'] >= $selectedTimePartsParsed['from']['minutes'] &&
                $fromToParts['to']['hours'] <= $selectedTimePartsParsed['to']['hours'] &&
                $fromToParts['to']['minutes'] <= $selectedTimePartsParsed['to']['minutes']
            ) {
                $compatibleTime[$index] = $timeLimit;
            }
        }

        if ($fullyCompatibleTime === null) {
            if (empty($compatibleTime)) {
                throw new LocalizedException(__('Compatible time limit not found'));
            }
            $fullyCompatibleTime = array_pop($compatibleTime);
        }

        return $fullyCompatibleTime;
    }

    /**
     * @param \Magento\Framework\DataObject $rate
     * @return string
     */
    public function getMethodCodeByRate(\Magento\Framework\DataObject $rate): string
    {
        return $rate->getCarrier() . '_' . $rate->getMethod();
    }
}
