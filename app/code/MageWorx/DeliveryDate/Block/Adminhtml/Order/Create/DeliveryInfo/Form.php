<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml\Order\Create\DeliveryInfo;

use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\AbstractEntity
     */
    protected $entity;

    /**
     * Queue object
     *
     * @var \MageWorx\DeliveryDate\Api\Data\QueueDataInterface
     */
    protected $queueData;

    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;

    /**
     * @var \MageWorx\DeliveryDate\Api\Data\QueueDataInterfaceFactory
     */
    protected $queueDataInterfaceFactory;

    /**
     * @var \MageWorx\DeliveryDate\Api\QueueManagerInterface
     */
    protected $queueManager;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \MageWorx\DeliveryDate\Api\Data\QueueDataInterfaceFactory $queueDataInterfaceFactory
     * @param \MageWorx\DeliveryDate\Api\QueueManagerInterface $queueManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \MageWorx\DeliveryDate\Api\Data\QueueDataInterfaceFactory $queueDataInterfaceFactory,
        \MageWorx\DeliveryDate\Api\QueueManagerInterface $queueManager,
        array $data = []
    ) {
        $this->sessionQuote              = $sessionQuote;
        $this->queueDataInterfaceFactory = $queueDataInterfaceFactory;
        $this->queueManager              = $queueManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepares form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareForm()
    {
        parent::_prepareForm();
        $form     = $this->_formFactory->create();
        $fieldset = $form->addFieldset('main', ['no_container' => true]);
        $form->setHtmlIdPrefix($this->_getFieldIdPrefix());

        $fieldset->addField(
            'entity_id',
            'hidden',
            [
                'name'     => $this->_getFieldName('entity_id'),
                'required' => false,
                'class'    => 'admin__field-option'
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            QueueDataInterface::DELIVERY_DAY_KEY,
            'date',
            [
                'date_format'   => $dateFormat,
                'name'          => $this->_getFieldName(QueueDataInterface::DELIVERY_DAY_KEY),
                'label'         => __('Delivery Day'),
                'required'      => false,
                'class'         => 'admin__field-option validate-date validate-delivery-day'
            ]
        );

        $deliveryTimeFrom = $fieldset->addField(
            'delivery_time_from',
            'text',
            [
                'name'     => $this->_getFieldName('delivery_time_from'),
                'label'    => __('Time From'),
                'required' => false,
                'class'    => 'admin__field-option validate-delivery-time',
                'value'    => '12:00'
            ]
        );

        $deliveryTimeTo = $fieldset->addField(
            'delivery_time_to',
            'text',
            [
                'name'     => $this->_getFieldName('delivery_time_to'),
                'label'    => __('Time To'),
                'required' => false,
                'class'    => 'admin__field-option validate-delivery-time',
                'value'    => '12:30'
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            \MageWorx\DeliveryDate\Block\Adminhtml\Widget\TimeInput::class
        );
        /** @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $renderer */
        $deliveryTimeFrom->setRenderer($renderer);
        $deliveryTimeTo->setRenderer($renderer);

        $fieldset->addField(
            QueueDataInterface::DELIVERY_COMMENT_KEY,
            'textarea',
            [
                'name'     => $this->_getFieldName(QueueDataInterface::DELIVERY_COMMENT_KEY),
                'label'    => __('Comment'),
                'required' => false,
                'class'    => 'admin__field-option input-text'
            ]
        );

        // Overridden default data with edited when block reloads by Ajax
        $this->_applyPostData();
        $form->setValues($this->getQueueData()->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Retrieve field html id prefix
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getFieldIdPrefix()
    {
        return 'queue_' . $this->getEntity()->getId() . '_';
    }

    /**
     * Retrieve entity for form
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity|\Magento\Framework\DataObject|\Magento\Quote\Model\Quote|\Magento\Sales\Model\Order
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entity for form
     *
     * @param \Magento\Framework\DataObject $entity
     * @return $this
     */
    public function setEntity(\Magento\Framework\DataObject $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Retrieve real name for field
     *
     * @param string $name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getFieldName($name)
    {
        return 'order[delivery_info][' . $name . ']';
    }

    /**
     * Applies posted data to queue
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _applyPostData()
    {
        $orderData = $this->getRequest()->getParam('order');
        if (!empty($orderData) && !empty($orderData['shipping_address'])) {
            $data = [
                QueueDataInterface::DELIVERY_DAY_KEY          =>
                    $orderData['delivery_info'][QueueDataInterface::DELIVERY_DAY_KEY] ?? null,
                QueueDataInterface::DELIVERY_HOURS_FROM_KEY   =>
                    $orderData['delivery_info'][QueueDataInterface::DELIVERY_HOURS_FROM_KEY] ?? null,
                QueueDataInterface::DELIVERY_MINUTES_FROM_KEY =>
                    $orderData['delivery_info'][QueueDataInterface::DELIVERY_MINUTES_FROM_KEY] ?? null,
                QueueDataInterface::DELIVERY_HOURS_TO_KEY     =>
                    $orderData['delivery_info'][QueueDataInterface::DELIVERY_HOURS_TO_KEY] ?? null,
                QueueDataInterface::DELIVERY_MINUTES_TO_KEY   =>
                    $orderData['delivery_info'][QueueDataInterface::DELIVERY_MINUTES_TO_KEY] ?? null,
                QueueDataInterface::DELIVERY_COMMENT_KEY      =>
                    $orderData['delivery_info'][QueueDataInterface::DELIVERY_COMMENT_KEY] ?? null
            ];

            $this->getQueueData()->addData($data);
        }

        return $this;
    }

    /**
     * Retrieve queue for entity
     *
     * @return \MageWorx\DeliveryDate\Api\Data\QueueDataInterface|\Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQueueData()
    {
        if ($this->queueData === null) {
            $this->initQueue();
        }

        return $this->queueData;
    }

    /**
     * Initialize queue for entity
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initQueue()
    {
        if (!$this->getEntity()) {
            return $this;
        }

        $shippingAddress = $this->getEntity()->getShippingAddress();
        if ($shippingAddress instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $shippingAddressId = $this->getEntity()->getShippingAddress()->getId();
            $queue             = $this->queueManager->getByQuoteAddressId($shippingAddressId);
        } elseif ($shippingAddress instanceof \Magento\Sales\Api\Data\OrderAddressInterface) {
            $shippingAddressId = $this->getEntity()->getShippingAddress()->getId();
            $queue             = $this->queueManager->getByOrderAddressId($shippingAddressId);
        }

        if (empty($queue)) {
            $queue = $this->queueDataInterfaceFactory->create();
        } else {
            $this->addDeliveryTimeStringToQueue($queue);
        }

        $this->queueData = $queue;

        return $this;
    }

    /**
     * Adds delivery_time_from and delivery_time_to to the queue data (as a formatted strings)
     *
     * @param QueueDataInterface $queue
     */
    protected function addDeliveryTimeStringToQueue(QueueDataInterface $queue)
    {
        if ($queue->getDeliveryHoursFrom() !== null) {
            $hoursFrom   = (string)$queue->getDeliveryHoursFrom();
            $minutesFrom = (string)$queue->getDeliveryMinutesFrom();
            if (strlen($minutesFrom) < 2) {
                $minutesFrom = '0' . $minutesFrom;
            }
            $queue->setData(
                'delivery_time_from',
                implode(
                    QueueDataInterface::TIME_DELIMITER,
                    [
                        $hoursFrom,
                        $minutesFrom
                    ]
                )
            );
        }

        if ($queue->getDeliveryHoursTo() !== null) {
            $hoursTo   = (string)$queue->getDeliveryHoursTo();
            $minutesTo = (string)$queue->getDeliveryMinutesTo();
            if (strlen($minutesTo) < 2) {
                $minutesTo = '0' . $minutesTo;
            }
            $queue->setData(
                'delivery_time_to',
                implode(
                    QueueDataInterface::TIME_DELIMITER,
                    [
                        $hoursTo,
                        $minutesTo
                    ]
                )
            );
        }
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function getSession()
    {
        return $this->sessionQuote;
    }

    /**
     * Retrieve real html id for field
     *
     * @param string $id
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getFieldId($id)
    {
        return $this->_getFieldIdPrefix() . $id;
    }
}
