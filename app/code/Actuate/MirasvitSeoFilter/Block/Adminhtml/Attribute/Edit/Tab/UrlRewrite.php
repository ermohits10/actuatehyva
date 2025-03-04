<?php

namespace Actuate\MirasvitSeoFilter\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\SeoFilter\Model\ConfigProvider;

class UrlRewrite extends \Mirasvit\SeoFilter\Block\Adminhtml\Attribute\Edit\Tab\UrlRewrite
{
    private $formFactory;

    /** @var Attribute */
    private $attribute;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory
    ) {
        parent::__construct($context, $registry, $formFactory);
        $this->attribute   = $registry->registry('entity_attribute');
        $this->formFactory = $formFactory;
    }

    protected function _prepareForm(): \Mirasvit\SeoFilter\Block\Adminhtml\Attribute\Edit\Tab\UrlRewrite
    {
        $form = $this->formFactory->create()->setData([
            'id'                => 'edit_form',
            'action'            => $this->getData('action'),
            'method'            => 'post',
            'enctype'           => 'multipart/form-data',
            'field_name_suffix' => 'seo_filter',
        ]);

        if (in_array($this->attribute->getAttributeCode(), ConfigProvider::ATTRIBUTES_EXCEPTIONS)) {
            $form->addFieldset('base_fieldset', [
                'legend' => __('SEO Filters configuration is not available for this attribute'),
                'class'  => 'fieldset-wide',
            ]);

            $this->setForm($form);

            return parent::_prepareForm();
        }

        $frontendInput = $this->attribute->getFrontendInput();

        $form->addField('attribute_code', 'hidden', [
            'name'  => 'attribute_code',
            'value' => $this->attribute->getAttributeCode(),
        ]);

        $form->addField('attribute', \Mirasvit\SeoFilter\Block\Adminhtml\Attribute\Edit\Tab\Fieldset\AttributeFieldset::class, [
            Attribute::class => $this->attribute,
        ]);

        if ($this->attribute->getAttributeCode() === 'category_ids') {
            $options = $this->getLayout()->createBlock(Fieldset\CategoryFieldSet::class);

            $this->setChild('form_after', $options);
        }

        if (in_array($frontendInput, ['select', 'multiselect'])) {
            $options = $this->getLayout()->createBlock(Fieldset\OptionsFieldset::class);

            $this->setChild('form_after', $options);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
