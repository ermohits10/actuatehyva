<?php

namespace ReliantDirect\Checkout\Magewire;

use Exception;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magewirephp\Magewire\Component;
use Mageside\SubscribeAtCheckout\Helper\Config as Helper;
use Mageside\SubscribeAtCheckout\Model\Config\Source\CheckoutSubscribe;

class NewsletterSubscribe extends Component
{
    public $subscribe = 0;

    protected SessionCheckout $sessionCheckout;
    protected CartRepositoryInterface $quoteRepository;

    /**
     * @var Helper
     */
    protected Helper $_helper;

    public function __construct(
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $quoteRepository,
        Helper $helper
    ) {
        $this->sessionCheckout = $sessionCheckout;
        $this->quoteRepository = $quoteRepository;
        $this->_helper = $helper;
    }

    public function updatingSubscribe($value)
    {
        try {

            if ($this->_helper->getConfigModule('enabled')) {
                $newsletterSubscribe = 0;

                if (in_array(
                    $this->_helper->getConfigModule('checkout_subscribe'),
                    [CheckoutSubscribe::FORCE_INVISIBLE, CheckoutSubscribe::FORCE]
                )) {
                    $newsletterSubscribe = 1;
                } elseif ($value) {
                    $newsletterSubscribe = $value;
                }

                $quote = $this->sessionCheckout->getQuote();
                $quote->setNewsletterSubscribe($newsletterSubscribe);

                $this->quoteRepository->save($quote);
            }
        } catch (LocalizedException| Exception $exception) {
            $this->dispatchErrorMessage('Something went wrong while saving your order comment. Please try again.');
        }

        return $value;
    }
}
