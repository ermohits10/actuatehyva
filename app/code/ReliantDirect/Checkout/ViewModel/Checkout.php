<?php

namespace ReliantDirect\Checkout\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class Checkout implements ArgumentInterface
{
    private ScopeConfigInterface $scopeConfig;
    private EncryptorInterface $encryptor;
    private Escaper $escaper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param Escaper $escaper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Escaper $escaper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->escaper = $escaper;
    }

    /**
     * @return bool
     */
    public function isUkPostCodeLookUpEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue('fetchify_pcl/options/enabled');
    }

    /**
     * @return array|string|null
     */
    public function getAccessToken()
    {
        $token = $this->scopeConfig->getValue(
            'fetchify_main/main_options/accesstoken',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        try {
            if (0 == preg_match("/^([a-zA-Z0-9]{5}-){3}[a-zA-Z0-9]{5}$/", $token)) {
                $token = $this->encryptor->decrypt($token);
            }
        } catch (\Exception $e) {
        }

        $token = $this->escaper->escapeHtml($token);
        if ($token) {
            return $token;
        }

        return null;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
