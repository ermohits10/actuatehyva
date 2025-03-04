<?php

namespace Actuate\ReliantDirectTheme\Model;

use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;

class AgreementsConfigProvider extends \Magento\CheckoutAgreements\Model\AgreementsConfigProvider
{
    /**
     * @var CheckoutAgreementsListInterface
     */
    private $checkoutAgreementsList;

    /**
     * @var ActiveStoreAgreementsFilter
     */
    private $activeStoreAgreementsFilter;

    /**
     * @param ScopeConfigInterface $scopeConfiguration
     * @param CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param Escaper $escaper
     * @param CheckoutAgreementsListInterface|null $checkoutAgreementsList
     * @param ActiveStoreAgreementsFilter|null $activeStoreAgreementsFilter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfiguration,
        CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        Escaper $escaper,
        CheckoutAgreementsListInterface $checkoutAgreementsList = null,
        ActiveStoreAgreementsFilter $activeStoreAgreementsFilter = null
    ) {
        parent::__construct(
            $scopeConfiguration,
            $checkoutAgreementsRepository,
            $escaper,
            $checkoutAgreementsList,
            $activeStoreAgreementsFilter
        );
        $this->checkoutAgreementsList = $checkoutAgreementsList ?: ObjectManager::getInstance()->get(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->activeStoreAgreementsFilter = $activeStoreAgreementsFilter ?: ObjectManager::getInstance()->get(
            ActiveStoreAgreementsFilter::class
        );
    }

    /**
     * Returns agreements config.
     *
     * @return array
     */
    protected function getAgreementsConfig()
    {
        $agreementConfiguration = [];
        $isAgreementsEnabled = $this->scopeConfiguration->isSetFlag(
            AgreementsProvider::PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        $agreementsList = $this->checkoutAgreementsList->getList(
            $this->activeStoreAgreementsFilter->buildSearchCriteria()
        );
        $agreementConfiguration['isEnabled'] = (bool)($isAgreementsEnabled && count($agreementsList) > 0);

        foreach ($agreementsList as $agreement) {
            $agreementConfiguration['agreements'][] = [
                'content' => $agreement->getIsHtml()
                    ? $agreement->getContent()
                    : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                'checkboxText' => $agreement->getCheckboxText(),
                'mode' => $agreement->getMode(),
                'agreementId' => $agreement->getAgreementId(),
                'contentHeight' => $agreement->getContentHeight()
            ];
        }

        return $agreementConfiguration;
    }
}
