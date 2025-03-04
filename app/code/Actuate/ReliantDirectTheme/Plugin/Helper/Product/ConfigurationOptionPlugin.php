<?php

namespace Actuate\ReliantDirectTheme\Plugin\Helper\Product;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;

class ConfigurationOptionPlugin
{
    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $_productOptionFactory;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param Escaper $escaper
     */
    public function __construct(
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Framework\Stdlib\StringUtils $string,
        Escaper $escaper = null
    ) {
        $this->_productOptionFactory = $productOptionFactory;
        $this->filter = $filter;
        $this->string = $string;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    public function aroundGetFormattedOptionValue(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        callable $proceed,
        $optionValue,
        $params = null
    ) {
        // Init params
        if (!$params) {
            $params = [];
        }
        $maxLength = isset($params['max_length']) ? $params['max_length'] : null;
        $cutReplacer = isset($params['cut_replacer']) ? $params['cut_replacer'] : '...';

        // Proceed with option
        $optionInfo = [];

        // Define input data format
        if (is_array($optionValue)) {
            if (isset($optionValue['option_id'])) {
                $optionInfo = $optionValue;
                if (isset($optionInfo['value'])) {
                    $optionValue = $this->escaper->escapeHtml($optionInfo['value'], ['ul', 'li']);
                }
            } elseif (isset($optionValue['value'])) {
                $optionValue = $optionValue['value'];
            }
        }

        // Render customized option view
        if (isset($optionInfo['custom_view']) && $optionInfo['custom_view']) {
            $_default = ['value' => $optionValue];
            if (isset($optionInfo['option_type'])) {
                try {
                    $group = $this->_productOptionFactory->create()->groupFactory($optionInfo['option_type']);
                    return ['value' => $group->getCustomizedView($optionInfo)];
                } catch (\Exception $e) {
                    return $_default;
                }
            }
            return $_default;
        }

        // Truncate standard view
        if (is_array($optionValue)) {
            $truncatedValue = implode("\n", $optionValue);
            $truncatedValue = nl2br($truncatedValue);
            return ['value' => $truncatedValue];
        } else {
            if ($maxLength) {
                $truncatedValue = $this->filter->truncate($optionValue, ['length' => $maxLength, 'etc' => '']);
            } else {
                $truncatedValue = $optionValue;
            }
            $truncatedValue = nl2br($truncatedValue);
        }

        $result = ['value' => $truncatedValue];

        if ($maxLength && $this->string->strlen($optionValue) > $maxLength) {
            $result['value'] = $result['value'] . $cutReplacer;
            $optionValue = nl2br($optionValue);
            $result['full_view'] = $optionValue;
        }

        return $result;
    }
}
