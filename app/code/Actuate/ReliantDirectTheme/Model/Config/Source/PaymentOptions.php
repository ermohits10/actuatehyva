<?php

namespace Actuate\ReliantDirectTheme\Model\Config\Source;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Api\PaymentMethodListInterface;

class PaymentOptions implements OptionSourceInterface
{
    /**
     * @var PaymentMethodListInterface
     */
    protected PaymentMethodListInterface $paymentMethodList;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @param PaymentMethodListInterface $paymentMethodList
     * @param RequestInterface $request
     */
    public function __construct(
        PaymentMethodListInterface $paymentMethodList,
        RequestInterface $request
    ) {
        $this->paymentMethodList = $paymentMethodList;
        $this->request = $request;
    }

    /**
     * @return \Magento\Payment\Api\Data\PaymentMethodInterface[]
     */
    public function getAvailablePaymentMethods()
    {
        $storeId = (int) $this->request->getParam('store');
        return $this->paymentMethodList->getActiveList($storeId);
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $paymentMethods = $this->getAvailablePaymentMethods();

        $options[] = [
            'label' => '',
            'value' => ''
        ];

        foreach ($paymentMethods as $paymentMethod) {
            $options[] = [
                'label' => $paymentMethod->getTitle() . ' (' . $paymentMethod->getCode() . ')',
                'value' => $paymentMethod->getCode()
            ];
        }

        return $options;
    }
}
