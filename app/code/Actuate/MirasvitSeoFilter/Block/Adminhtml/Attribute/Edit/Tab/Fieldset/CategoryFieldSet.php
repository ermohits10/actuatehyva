<?php

namespace Actuate\MirasvitSeoFilter\Block\Adminhtml\Attribute\Edit\Tab\Fieldset;

use Actuate\MirasvitSeoFilter\ViewModel\FilterOptionViewModel;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Context;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class CategoryFieldSet extends Widget
{
    private $attribute;
    private Config $eavConfig;
    private FilterOptionViewModel $filterOptionViewModel;

    /**
     * @param Context $context
     * @param Config $eavConfig
     * @param Registry $registry
     * @param FilterOptionViewModel $filterOptionViewModel
     */
    public function __construct(
        Context $context,
        Config $eavConfig,
        Registry $registry,
        FilterOptionViewModel $filterOptionViewModel
    ) {
        parent::__construct($context);
        $this->attribute = $registry->registry('entity_attribute');
        $this->eavConfig = $eavConfig;
        $this->filterOptionViewModel = $filterOptionViewModel;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getOptions():array
    {
        $attribute = $this->getAttribute();
        $indexableData = $this->filterOptionViewModel->getExistsOptions($attribute->getAttributeCode());

        if (!empty($indexableData)) {
            return array_keys($indexableData);
        }
        return [];
    }

    protected function _construct(): void
    {
        parent::_construct();

        $this->setTemplate('Actuate_MirasvitSeoFilter::product/attribute/tab/fieldset/category.phtml');
    }

    /**
     * @return AbstractAttribute
     * @throws LocalizedException
     */
    private function getAttribute(): AbstractAttribute
    {
        return $this->eavConfig->getAttribute('catalog_product', $this->attribute->getAttributeCode());
    }
}
