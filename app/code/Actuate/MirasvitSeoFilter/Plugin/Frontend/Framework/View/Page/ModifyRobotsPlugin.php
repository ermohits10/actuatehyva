<?php

namespace Actuate\MirasvitSeoFilter\Plugin\Frontend\Framework\View\Page;

use Actuate\MirasvitSeoFilter\ViewModel\FilterOptionViewModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Config\Interceptor;

class ModifyRobotsPlugin
{
    private RequestInterface $request;
    private FilterOptionViewModel $filterOptionViewModel;
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param RequestInterface $request
     * @param FilterOptionViewModel $filterOptionViewModel
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        RequestInterface $request,
        FilterOptionViewModel $filterOptionViewModel,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->filterOptionViewModel = $filterOptionViewModel;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Interceptor $subject
     * @param string $result
     * @return string
     */
    public function afterGetRobots(Interceptor $subject, string $result): string
    {
        return $this->getRobots($result);
    }

    /**
     * @return bool
     */
    private function isRobotsUpdateWithFollow(): bool
    {
        return (bool) $this->scopeConfig->getValue('actuate_seo_filter/general/robots_enable');
    }

    /**
     * @param $result
     * @return string
     */
    private function getRobots($result): string
    {
        if (!str_contains(str_replace(' ', '', strtolower($result)), 'index,follow')
            && $this->request->getFullActionName() === 'catalog_category_view'
            && $this->isRobotsUpdateWithFollow()) {
            $allParams = $this->request->getParams();
            $seoIndexData = $this->filterOptionViewModel->getExistIndexableDataByAttributes(array_keys($allParams));
            $existsData = array_column($seoIndexData, 'is_indexable', 'option');

            $filterParams = [];
            foreach ($allParams as $attributeCode => $value) {
                if ($attributeCode === 'id') { continue; }
                $values = explode(',', $value);
                foreach ($values as $filterValue) {
                    $filterParams[$filterValue] = $existsData[$filterValue] ?? "0";
                }
            }
            if (!in_array("0", $filterParams)) {
                $result = 'INDEX,FOLLOW';
            }
        }
        return $result;
    }
}
