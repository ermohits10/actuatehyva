<?php

namespace Actuate\Option\Block\Adminhtml\Form\Field;

use Magento\Framework\Exception\LocalizedException;

class Options extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('short_title', ['label' => __('Short Title'), 'renderer' => false, 'class' => 'short_title' ]);
        $this->addColumn('identifier', ['label' => __('CMS Identifier'), 'renderer' => false, 'class' => 'identifier' ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add New');
    }
}
