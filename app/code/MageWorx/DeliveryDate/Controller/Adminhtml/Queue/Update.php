<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Controller\Adminhtml\Queue;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Shipment;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;

class Update extends Action
{
    /**
     * @var \MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var \MageWorx\DeliveryDate\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    private $filterDate;

    /**
     * Update constructor.
     *
     * @param Action\Context $context
     * @param \MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface $queueRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate
     * @param \MageWorx\DeliveryDate\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \MageWorx\DeliveryDate\Api\Repository\QueueRepositoryInterface $queueRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate,
        \MageWorx\DeliveryDate\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->queueRepository    = $queueRepository;
        $this->orderRepository    = $orderRepository;
        $this->invoiceRepository  = $invoiceRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->filterDate         = $filterDate;
        $this->helper             = $helper;
        $this->logger             = $logger;
    }

    /**
     * Create and return instance of redirect to the referrer url
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function redirectBack()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            return $this->redirectBack();
        }

        $id = $this->detectQueueId();
        if ($id) {
            try {
                $model = $this->queueRepository->getById($id);
            } catch (NoSuchEntityException $e) {
                $model = $this->queueRepository->getEmptyEntity();
            } catch (LocalizedException $e) {
                $model = $this->queueRepository->getEmptyEntity();
            }
        } else {
            $model = $this->queueRepository->getEmptyEntity();
        }

        try {
            $data = $this->getRequest()->getPostValue();
            $data = $this->prepareData($data);

            if (empty($data)) {
                throw new LocalizedException(__('Unable to locate delivery info data in request'));
            }

            /** @var Order|Invoice|Shipment $entity */
            $entity                                         = $this->getEntity();
            $data[QueueDataInterface::STORE_ID_KEY]         = $entity->getStoreId();
            $data[QueueDataInterface::QUOTE_ADDRESS_ID_KEY] = $entity->getQuoteAddressId();
            $data[QueueDataInterface::ORDER_ADDRESS_ID_KEY] = $entity->getShippingAddress()->getId();
            $data[QueueDataInterface::SHIPPING_METHOD_KEY]  = $entity->getShippingMethod(false);
            $shippingMethod                                 = $entity->getShippingMethod(true);
            if ($shippingMethod) {
                $data[QueueDataInterface::CARRIER_KEY] = $shippingMethod->getData('carrier_code');
            }

            $model->addData($data);
            $this->_session->setPageData($model->getData());

            $this->queueRepository->save($model);
            $this->messageManager->addSuccessMessage(__('Delivery info successfully updated.'));
            $this->_session->setPageData(false);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the delivery info data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);
        }

        return $this->redirectBack();
    }

    /**
     * Detects and return entity
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Api\Data\ShipmentInterface
     * @throws LocalizedException
     */
    private function getEntity()
    {
        $sourceType = $this->_request->getParam('source_type');
        $sourceId   = $this->_request->getParam('source_id');
        if (!$sourceId) {
            throw new LocalizedException(__('Invalid source id %1', $sourceId));
        }

        switch ($sourceType) {
            case 'order':
                $entity = $this->orderRepository->get($sourceId);
                break;
            case 'invoice':
                $entity = $this->invoiceRepository->get($sourceId);
                break;
            case 'shipment':
                $entity = $this->shipmentRepository->get($sourceId);
                break;
            default:
                throw new LocalizedException(__('Unknown source type %1', $sourceType));
        }

        return $entity;
    }

    /**
     * @return int|null
     */
    private function detectQueueId()
    {
        if ($this->getRequest()->getParam('entity_id')) {
            return (int)$this->getRequest()->getParam('entity_id');
        }

        $data = $this->getRequest()->getParam('order');
        if (!empty($data['delivery_info']['entity_id'])) {
            return (int)$data['delivery_info']['entity_id'];
        }

        return null;
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        $data = !empty($data['order']['delivery_info']) ? $data['order']['delivery_info'] : [];

        if (isset($data['entity_id'])) {
            unset($data['entity_id']);
        }

        if (isset($data['delivery_day'])) {
            $day                  = $this->filterDate->filter($data['delivery_day']);
            $data['delivery_day'] = $day;
        }

        if (isset($data['delivery_comment'])) {
            $data['delivery_comment'] = strip_tags($data['delivery_comment']);
        }

        if (isset($data['delivery_time_from']) || isset($data['delivery_time_to'])) {
            $from     = (string)$data['delivery_time_from'];
            $to       = (string)$data['delivery_time_to'];
            $timeData = [
                QueueDataInterface::DELIVERY_HOURS_FROM_KEY   => $this->helper->getPart($from, 0),
                QueueDataInterface::DELIVERY_MINUTES_FROM_KEY => $this->helper->getPart($from, 1),
                QueueDataInterface::DELIVERY_HOURS_TO_KEY     => $this->helper->getPart($to, 0),
                QueueDataInterface::DELIVERY_MINUTES_TO_KEY   => $this->helper->getPart($to, 1),
            ];
            $data     = array_merge($data, $timeData);
        }

        return $data;
    }
}
