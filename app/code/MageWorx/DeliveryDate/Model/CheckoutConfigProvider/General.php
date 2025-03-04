<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Model\CheckoutConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageWorx\DeliveryDate\Api\DeliveryManagerInterface;
use MageWorx\DeliveryDate\Exceptions\DeliveryTimeException;
use MageWorx\DeliveryDate\Helper\Data as Helper;

class General implements ConfigProviderInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var DeliveryManagerInterface
     */
    private $deliveryManager;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * General constructor.
     *
     * @param Helper $helper
     * @param DeliveryManagerInterface $deliveryManager
     * @param CheckoutSession $checkoutSession
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $resolver
     */
    public function __construct(
        Helper $helper,
        DeliveryManagerInterface $deliveryManager,
        CheckoutSession $checkoutSession,
        TimezoneInterface $timezone,
        ResolverInterface $resolver
    ) {
        $this->helper          = $helper;
        $this->deliveryManager = $deliveryManager;
        $this->checkoutSession = $checkoutSession;
        $this->timezone        = $timezone;
        $this->resolver        = $resolver;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $output = [];

        \Magento\Framework\Profiler::start('get_delivery_date_config_general');
        if ($this->helper->isEnabled()) {
            // If method is not available - we do not display it on the checkout.
            try {
                $quote = $this->checkoutSession->getQuote();
                if (!$quote) {
                    return $output;
                }

                $deliveryDatesByMethod = $this->deliveryManager->getAvailableLimitsForQuote($quote);

                $output['mageworx']['delivery_date'] = !empty($output['mageworx']['delivery_date']) && is_array(
                    $output['mageworx']['delivery_date']
                ) ?
                    $output['mageworx']['delivery_date'] :
                    [];
                $output['mageworx']['delivery_date'] = array_merge(
                    $output['mageworx']['delivery_date'],
                    $deliveryDatesByMethod
                );

                $nowDate = new \DateTime(
                    'now',
                    new \DateTimeZone($this->timezone->getConfigTimezone())
                );

                $output['mageworx']['delivery_date']['required']    = $this->helper->isDeliveryDateRequired();
                $output['mageworx']['delivery_date']['preselected'] = $this->helper->isPreSelectDeliveryDateEnabled();

                $output['mageworx']['delivery_date']['timezone']             = $this->timezone->getConfigTimezone();
                $output['mageworx']['delivery_date']['locale']               = $this->getSimplifiedLocale();
                $output['mageworx']['delivery_date']['serverTimezoneOffset'] = $this->timezone->date()->getOffset();
                $output['mageworx']['delivery_date']['now']                  = $nowDate->format('Y/m/d H:i:s');
            } catch (DeliveryTimeException $deliveryTimeException) {
                // Delivery Time Selection feature was blocked by product configuration
                $output['mageworx']['delivery_date'] = [];
            } catch (\Exception $e) {
                $output['mageworx']['delivery_date'] = [];
            }
        } else {
            $output['mageworx']['delivery_date'] = [];
        }

        \Magento\Framework\Profiler::stop('get_delivery_date_config_general');

        return $output;
    }

    /**
     * Get locale string like en, ru, fr
     *
     * @return string
     */
    public function getSimplifiedLocale(): string
    {
        $baseLocale = $this->resolver->getLocale();
        $localePart = mb_substr($baseLocale, 0, -2);

        return mb_strtolower($localePart);
    }
}
