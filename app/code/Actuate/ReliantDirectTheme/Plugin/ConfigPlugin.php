<?php

namespace Actuate\ReliantDirectTheme\Plugin;

class ConfigPlugin
{
    /**
     * @var \Magento\Framework\View\Helper\SecureHtmlRenderer
     */
    private $secureRenderer;

    public function __construct(
        \Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer
    ) {
        $this->secureRenderer = $secureRenderer;
    }

    public function afterGetIncludes(
        \Magento\Framework\View\Page\Config $subject,
        $result
    ) {
        if (!empty($result)) {
            $resultHtml = '';
            $pattern = '/<script\b[^>]*>([\s\S]*?)<\/script>/i';

            // Use preg_match_all to find all matches of the pattern in the HTML content
            if (preg_match_all($pattern, $result, $matches)) {
                foreach ($matches[1] as $scriptContent) {
                    // Display the extracted script content
                    if (!empty($scriptContent)) {
                        $resultHtml .= /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptContent, false);
                    }
                }
            }

            $pattern = '/<script\b[^>]*>([\s\S]*?)<\/script>/i';
            $result = preg_replace($pattern, '', $result);
            $result .= $resultHtml;
        }

        return $result;
    }
}
