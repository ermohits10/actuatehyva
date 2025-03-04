<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml\Widget;

use Magento\Framework\Data\Form\Element\AbstractElement;

class TimeInput extends \Magento\Framework\View\Element\Template implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface,
    \Magento\Widget\Block\BlockInterface
{
    /**
     * Form element which re-rendering
     *
     * @var \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected $element;

    /**
     * @var string
     */
    protected $_template = 'MageWorx_DeliveryDate::form/renderer/time.phtml';

    /**
     * Retrieve an element
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Render element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;

        return $this->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }

        return $this->fetchView($this->getTemplateFile());
    }
}
