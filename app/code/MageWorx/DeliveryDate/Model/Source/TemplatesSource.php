<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TemplatesSource implements OptionSourceInterface
{
    const DEFAULT_TEMPLATE_NAME = 'default';

    /**
     * @var array
     */
    private $templates;

    /**
     * DeliveryDateTemplates constructor.
     *
     * @param array $templates
     */
    public function __construct(
        $templates = []
    ) {
        $this->templates = $templates;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $templates = $this->getTemplates();
        foreach ($templates as $name => $value) {
            $label          = !empty($value['backend_label']) ? $value['backend_label'] : __(ucfirst($name));
            $options[$name] = [
                'label' => $label,
                'value' => $name
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param string|null $name
     * @return array
     */
    public function getTemplateData($name = null)
    {
        $templates = $this->getTemplates();
        if (empty($templates[mb_strtolower($name)])) {
            return $templates[static::DEFAULT_TEMPLATE_NAME];
        }

        return $templates[mb_strtolower($name)];
    }
}
