<?php

namespace ReliantDirect\Checkout\Plugin\Model\Magewire\Payment;

use Magento\Checkout\Model\Session as SessionCheckout;
use Hyva\Checkout\Model\Magewire\Payment\PlaceOrderServiceProvider;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Magento\Framework\App\ObjectManager;

class PaypalExpressPlaceOrderService
{
    protected $sessionCheckout;

    protected $placeOrderServiceProvider;

    protected $evaluationResultFactory;

    public function __construct(
        SessionCheckout $sessionCheckout,
        PlaceOrderServiceProvider $placeOrderServiceProvider,
        EvaluationResultFactory $evaluationResultFactory = null
    )
    {
        $this->sessionCheckout = $sessionCheckout;
        $this->placeOrderServiceProvider = $placeOrderServiceProvider;
        $this->evaluationResultFactory = $evaluationResultFactory
            ?: ObjectManager::getInstance()->get(EvaluationResultFactory::class);
    }

    public function afterProcess(\Hyva\Checkout\Model\Magewire\Payment\PlaceOrderServiceProcessor $subject, $result,$component, $quote , $data )
    {
        $quote = $quote ?? $this->sessionCheckout->getQuote();
        if ($quote->getPayment()->getMethod() == 'paypal_express') {
            $this->sessionCheckout->setIsIgnoreTermAndCondition(true);
            /** @var AbstractPlaceOrderService $placeOrderService */
            $placeOrderService = $this->placeOrderServiceProvider->getByPayment($quote->getPayment());
            $component->getEvaluationResultBatch()->push(
                $this->evaluationResultFactory->createRedirect(
                    $placeOrderService->getRedirectUrl($quote, null)
                )
                    ->withTimeout(3000)
                    ->dispatch()
            );
        }

        return $result;
    }
}