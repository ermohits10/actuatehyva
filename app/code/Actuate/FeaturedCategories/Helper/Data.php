<?php
namespace Actuate\FeaturedCategories\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}