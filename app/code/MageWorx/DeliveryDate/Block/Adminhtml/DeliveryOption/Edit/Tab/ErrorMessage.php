<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml\DeliveryOption\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface;
use MageWorx\DeliveryDate\Controller\Adminhtml\DeliveryOption as DeliveryOptionController;
use MageWorx\DeliveryDate\Model\DeliveryOption;

class ErrorMessage extends Generic implements
    TabInterface
{
    const DEFAULT_TAB_LABEL = 'Error Messages';
    const DEFAULT_TAB_TITLE = 'Error Messages';

    /**
     * @var string
     */
    protected $dataFormPart = 'delivery_option_form';

    /**
     * @var string
     */
    protected $_nameInLayout = 'store_view_error_message';

    /**
     * @var DeliveryOptionRepositoryInterface
     */
    protected $deliveryOptionRepository;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param DeliveryOptionRepositoryInterface $deliveryOptionRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        DeliveryOptionRepositoryInterface $deliveryOptionRepository,
        array $data = []
    ) {
        $this->deliveryOptionRepository = $deliveryOptionRepository;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var DeliveryOption $carrier */
        $deliveryOption = $this->resolveDeliveryOption();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('delivery_option_');

        if (!$this->_storeManager->isSingleStoreMode()) {
            $errorMessages = $deliveryOption->getDeliveryDateRequiredErrorMessages();
            $this->_createStoreSpecificFieldset($form, $errorMessages);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Resolve delivery option entity loaded or empty
     *
     * @return DeliveryOption
     */
    private function resolveDeliveryOption()
    {
        /** @var DeliveryOption $model */
        $model = $this->_coreRegistry->registry(DeliveryOptionController::REGISTRY_ID);
        if ($model) {
            return $model;
        }

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model = $this->deliveryOptionRepository->getById($id);
        } else {
            $model = $this->deliveryOptionRepository->getEmptyEntity();
        }

        return $model;
    }

    /**
     * Is need to show this tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        if (!$this->_storeManager->isSingleStoreMode()) {
            return true;
        }

        return false;
    }

    /**
     * Is tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get current tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        $label = static::DEFAULT_TAB_LABEL;

        return __($label);
    }

    /**
     * Get default tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        $title = static::DEFAULT_TAB_TITLE;

        return __($title);
    }

    /**
     * Get default tab class
     *
     * @return null
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Get tab url
     *
     * @return null
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Is tab loaded via ajax
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Create store specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $labels
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected function _createStoreSpecificFieldset($form, $labels)
    {
        $fieldset = $form->addFieldset(
            'store_view_error_message',
            [
                'legend' => __('"Delivery Date Required" error message'),
                'class'  => 'store-scope',
            ]
        );

        /** @var \MageWorx\ShippingRules\Block\Adminhtml\Store\Switcher\Form\Renderer\Fieldset $renderer */
        $renderer = $this->getLayout()
                         ->createBlock(
                             \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset::class,
                             'delivery_date_required_error_message',
                             [
                                 'data' => [
                                         'template' => 'MageWorx_DeliveryDate::form/renderer/fieldset.phtml'
                                     ]
                             ]
                         );

        $fieldset->setRenderer($renderer);
        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            $fieldId = "w_{$website->getId()}_label";
            $fieldset->addField(
                $fieldId,
                'note',
                [
                    'label'               => $website->getName(),
                    'fieldset_html_class' => 'website',
                    'class'               => 'website',
                    'css_class'           => 'website',
                ]
            );

            $groups = $website->getGroups();
            foreach ($groups as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }

                $groupFieldId = "sg_{$group->getId()}_label";
                $fieldset->addField(
                    $groupFieldId,
                    'note',
                    [
                        'label'               => $group->getName(),
                        'fieldset_html_class' => 'store-group',
                        'class'               => 'store-group',
                        'css_class'           => 'store-group',
                    ]
                );

                foreach ($stores as $store) {
                    $id             = $store->getId();
                    $storeFieldId   = "s_{$id}";
                    $storeFieldName = 'delivery_date_required_error_message[' . $id . ']';
                    $storeName      = $store->getName();

                    if (isset($labels[$id])) {
                        $storeValue = $labels[$id];
                    } else {
                        $storeValue = '';
                    }

                    $fieldset->addField(
                        $storeFieldId,
                        'text',
                        [
                            'name'                => $storeFieldName,
                            'title'               => $storeName,
                            'label'               => $storeName,
                            'required'            => false,
                            'value'               => $storeValue,
                            'fieldset_html_class' => 'store',
                            'data-form-part'      => $this->dataFormPart,
                            'class'               => 'store long_input',
                            'css_class'           => 'store long_input',
                        ]
                    );
                }
            }
        }

        return $fieldset;
    }
}
