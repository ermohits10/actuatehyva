<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Block\Adminhtml;

use Magento\Framework\Exception\LocalizedException;

class DeliveryInfoAbstract extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    protected $helper;

    /** @Important Next properties should be rewritten in a child class */
    /**
     * @var string
     */
    protected $sourceType;

    /**
     * @var bool
     */
    protected $isEditable;
    /** End; */

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param string $sourceType
     * @param bool $isEditable
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\DeliveryDate\Helper\Data $helper,
        $sourceType,
        $isEditable,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->helper     = $helper;
        $this->sourceType = $sourceType;
        $this->isEditable = (bool)$isEditable;
        if (!$this->sourceType) {
            throw new LocalizedException(
                __(
                    'Source type must be defined in class %1. For additional reference see class %2',
                    get_class($this),
                    __CLASS__
                )
            );
        }
    }

    /**
     * Get source model: invoice, shipment or order
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface|\Magento\Sales\Model\Order\Shipment|\Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Model\Order\Invoice|\Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSource()
    {
        if ($this->hasData($this->sourceType)) {
            return $this->getData($this->sourceType);
        }
        if ($this->coreRegistry->registry('current_' . $this->sourceType)) {
            return $this->coreRegistry->registry('current_' . $this->sourceType);
        }
        if ($this->coreRegistry->registry($this->sourceType)) {
            return $this->coreRegistry->registry($this->sourceType);
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('We can\'t get the %1 instance right now.', $this->sourceType)
        );
    }

    /**
     * Flag: is block will be editable
     *
     * @return bool
     */
    public function isEditable()
    {
        return $this->isEditable && $this->isDeliveryDateAvailable();
    }

    /**
     * Returns a source type: order, invoice, shipment
     *
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @return bool
     */
    public function isDeliveryDateAvailable()
    {
        try {
            $source = $this->getSource();
            if ($source->getIsVirtual()) {
                return false;
            }

            if (!$source->getShippingAddress()) {
                return false;
            }

            if ($source->getShippingAddress()->getOrder()->getIsVirtual()) {
                return false;
            }

            $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();
            if (!$shippingAddressExtensionAttributes->getDeliveryDay() &&
                $shippingAddressExtensionAttributes->getDeliveryHoursFrom() === null &&
                $shippingAddressExtensionAttributes->getDeliveryHoursTo() === null
            ) {
                return $this->helper->isEmptyDeliveryDateBlockVisible($source->getStoreId());
            }

            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * Get extension attributes object assigned to the shipping address
     *
     * @return \Magento\Sales\Api\Data\OrderAddressExtensionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShippingAddressExtensionAttributes()
    {
        $source                             = $this->getSource();
        $shippingAddress                    = $source->getShippingAddress();
        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        if ($shippingAddressExtensionAttributes === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to locate delivery date'));
        }

        return $shippingAddressExtensionAttributes;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFullDeliveryMessage()
    {
        $deliveryDateMessage = $this->getDeliveryDateMessage();
        $deliveryTimeMessage = $this->getDeliveryTimeMessage();

        return $deliveryDateMessage . ' ' . $deliveryTimeMessage;
    }

    /**
     * @return \Magento\Framework\Phrase
     * @throws LocalizedException
     */
    public function getDeliveryDateMessage()
    {
        $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();
        $deliveryDate                       = $shippingAddressExtensionAttributes->getDeliveryDay();

        if ($deliveryDate) {
            $deliveryDateMessage = $this->helper->formatDateFromDefaultToStoreSpecific(
                $deliveryDate,
                $this->getStoreId()
            );
        } else {
            $deliveryDateMessage = __('Delivery date was not set.');
        }

        return $deliveryDateMessage;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     * @throws LocalizedException
     */
    public function getDeliveryTimeMessage()
    {
        $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();

        $deliveryTimeArray = $this->helper->parseDeliveryDateArray($shippingAddressExtensionAttributes);
        if ($deliveryTimeArray === null) {
            return '';
        }

        $deliveryTimeMessage = $this->helper->getDeliveryTimeFormattedMessage(
            $deliveryTimeArray['from']['full'],
            $deliveryTimeArray['to']['full'],
            $this->getStoreId()
        );

        return $deliveryTimeMessage;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getDeliveryCommentMessage()
    {
        $shippingAddressExtensionAttributes = $this->getShippingAddressExtensionAttributes();
        $deliveryComment                    = $shippingAddressExtensionAttributes->getDeliveryComment();
        $result                             = '';
        if ($deliveryComment) {
            $result .= __('Comment: ') . $deliveryComment;
        }

        return $result;
    }

    /**
     * @return int|mixed|null
     * @throws LocalizedException
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }

        return $this->getSource()->getStoreId();
    }

    /**
     * Generate form for editing of delivery date
     *
     * @param \Magento\Framework\DataObject $entity
     * @param string $entityType
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormHtml(\Magento\Framework\DataObject $entity, $entityType = 'quote')
    {
        return $this->getLayout()->createBlock(
            \MageWorx\DeliveryDate\Block\Adminhtml\Order\Create\DeliveryInfo\Form::class
        )->setEntity(
            $entity
        )->setEntityType(
            $entityType
        )->toHtml();
    }

    /**
     * Return "Submit" button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getSubmitButtonHtml()
    {
        $html = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
                     ->setData(
                         [
                             'id'    => 'delivery-date-submit',
                             'label' => __('Submit'),
                             'type'  => 'button',
                             'class' => 'edit primary',
                             'style' => 'margin-top: 1em; float:right;',
                         ]
                     )
                     ->toHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getSubmitFormUrl()
    {
        $url = $this->getUrl('mageworx_deliverydate/queue/update');

        return $url;
    }
}
