<?php

namespace Actuate\Option\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magezon\Core\Framework\Serialize\Serializer\Json;

class OptionTooltipViewModel implements ArgumentInterface
{
    private ScopeConfigInterface $scopeConfig;
    private Json $json;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Json $json)
    {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
    }

    /**
     * @param $shortTitle
     * @return mixed|null
     */
    public function getOptionTooltip($shortTitle)
    {
        $optionTooltipMap = $this->scopeConfig->getValue('actuate_options/general/tooltip');
        if ($optionTooltipMap) {
            $optionTooltips = $this->json->unserialize($optionTooltipMap);
            $matchedTooltip = array_filter($optionTooltips, function($toolTip) use ($shortTitle) {
                return strtolower($toolTip['short_title']) === strtolower($shortTitle);
            });

            if (!empty($matchedTooltip)) {
                $matchedTooltip = array_values($matchedTooltip);
                return $matchedTooltip[0]['identifier'] ?? null;
            }
        }
        return null;
    }
}
