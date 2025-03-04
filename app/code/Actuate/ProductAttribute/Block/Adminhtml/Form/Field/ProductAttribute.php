<?php

namespace Actuate\ProductAttribute\Block\Adminhtml\Form\Field;

use Magento\Framework\Exception\LocalizedException;

class ProductAttribute extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('section_name', ['label' => __('Section Name'), 'renderer' => false, 'class' => 'section_name' ]);
        $this->addColumn('attribute_code', ['label' => __('Attribute Code'), 'renderer' => false, 'class' => 'attribute_code' ]);

        $this->_addAfter = true;
        $this->_addButtonLabel = __('Add New');
    }
}
