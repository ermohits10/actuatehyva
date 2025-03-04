<?php
namespace Actuate\ReliantDirectTheme\Block\Checkout;

use Amasty\CheckoutCore\Block\Onepage\LayoutWalkerFactory;
use Amasty\CheckoutCore\Model\Config;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Amasty\Checkout\Model\BillingAddress;
use Psr\Log\LoggerInterface;

class PostcodeProcessor implements LayoutProcessorInterface
{
    /**
     * @var Config
     */
    private $checkoutConfig;

    /**
     * @var LayoutWalkerFactory
     */
    private $walkerFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BillingAddress
     */
    private $billingAddress;

    private \Amasty\CheckoutCore\Block\Onepage\LayoutWalker $walker;

    /**
     * @param Config $checkoutConfig
     * @param LayoutWalkerFactory $walkerFactory
     * @param BillingAddress $billingAddress
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $checkoutConfig,
        LayoutWalkerFactory $walkerFactory,
        BillingAddress $billingAddress,
        LoggerInterface $logger
    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->walkerFactory = $walkerFactory;
        $this->billingAddress = $billingAddress;
        $this->logger = $logger;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->checkoutConfig->isEnabled()) {
            return $jsLayout;
        }

        try {
            $this->walker = $this->walkerFactory->create(['layoutArray' => $jsLayout]);

            $validations = $this->walker->getValue('{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.validation') ?? [];
            $validations['validate-zip-uk'] = true;
            $this->walker->setValue('{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.validation', $validations);

            $billingAddressPath = $this->billingAddress->getBillingPath($this->walker);
            foreach ($billingAddressPath as $path) {
                $billingValidations = $this->walker->getValue($path . '.postcode.validation') ?? [];
                $billingValidations['validate-zip-uk'] = true;
                $this->walker->setValue($path . '.postcode.validation', $billingValidations);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this->walker->getResult();
    }
}
