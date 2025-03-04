<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\DeliveryInfo\Edit;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

class SaveButton extends Field
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * SaveButton constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->formKey = $formKey;
    }

    /**
     * Add js listener to reset button
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();

        $this->_data['config']['actions'] = [
            [
                'actionName' => 'save',
                'targetName' => 'change_delivery_date_customer_form.change_delivery_date_customer_form',
                'params'     => []
            ]
        ];
    }
}
