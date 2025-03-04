<?php

declare(strict_types=1);

namespace Actuate\MirasvitSeoFilter\Block\Adminhtml\Attribute\Edit\Tab\Fieldset;

use Actuate\MirasvitSeoFilter\ViewModel\FilterOptionViewModel;
use Magento\Backend\Block\Widget\Context;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mirasvit\SeoFilter\Service\RewriteService;

class OptionsFieldset extends \Mirasvit\SeoFilter\Block\Adminhtml\Attribute\Edit\Tab\Fieldset\OptionsFieldset
{
    private RewriteService $rewriteService;
    private Config $eavConfig;
    private $attribute;
    private FilterOptionViewModel $filterOptionViewModel;

    /**
     * @param RewriteService $rewriteService
     * @param FormFactory $formFactory
     * @param Context $context
     * @param Config $eavConfig
     * @param Registry $registry
     * @param FilterOptionViewModel $filterOptionViewModel
     */
    public function __construct(
        RewriteService $rewriteService,
        FormFactory $formFactory,
        Context $context,
        Config $eavConfig,
        Registry $registry,
        FilterOptionViewModel $filterOptionViewModel
    ) {
        parent::__construct($rewriteService, $formFactory, $context, $eavConfig, $registry);
        $this->rewriteService = $rewriteService;
        $this->attribute = $registry->registry('entity_attribute');
        $this->eavConfig = $eavConfig;
        $this->filterOptionViewModel = $filterOptionViewModel;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getOptions(): array
    {
        $attribute = $this->getAttribute();

        $options = [];
        $indexableData = $this->filterOptionViewModel->getExistsOptions($attribute->getAttributeCode());
        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if (isset($option['value']) && $option['value']) {
                $optionId = $option['value'];
                $name     = (string)$option['label'];

                $option = [
                    'id'      => $optionId,
                    'value'   => $optionId,
                    'name'    => $name,
                    'rewrite' => [],
                    'is_indexable' => 0,
                ];

                foreach ($this->_storeManager->getStores() as $store) {
                    $storeId = (int)$store->getId();

                    $rewrite = $this->rewriteService->getOptionRewrite(
                        (string)$attribute->getAttributeCode(),
                        (string)$optionId,
                        $storeId,
                        false
                    );

                    $option['rewrite'][(int)$store->getId()] = $rewrite->getRewrite();
                    $option['indexable'] = $indexableData[$optionId] ?? 0;
                }

                $options[] = $option;
            }
        }

        return $options;
    }

    protected function _construct(): void
    {
        parent::_construct();

        $this->setTemplate('Actuate_MirasvitSeoFilter::product/attribute/tab/fieldset/options.phtml');
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
