<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\DeliveryDate\Api\Data\QueueDataInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date as FilterDate;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use Psr\Log\LoggerInterface;

class UpdateDeliveryInfoInRequestWhenSubmitOrder implements ObserverInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var FilterDate
     */
    private $filterDate;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ParseTimeWhenSubmitOrder constructor.
     *
     * @param Helper $helper
     * @param FilterDate $filterDate
     */
    public function __construct(
        Helper $helper,
        FilterDate $filterDate,
        LoggerInterface $logger
    ) {
        $this->helper     = $helper;
        $this->filterDate = $filterDate;
        $this->logger     = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getData('request_model');
        if (!$request instanceof RequestInterface) {
            return;
        }

        $data = $request->getParams();
        if (is_array($data) && isset($data['order'])) {
            $isModified = false;

            if (isset($data['order']['delivery_info']['delivery_time_from']) ||
                isset($data['order']['delivery_info']['delivery_time_to'])
            ) {
                $from = (string)$data['order']['delivery_info']['delivery_time_from'];
                $to   = (string)$data['order']['delivery_info']['delivery_time_to'];

                $timeData = [
                    QueueDataInterface::DELIVERY_HOURS_FROM_KEY   => $this->helper->getPart($from, 0),
                    QueueDataInterface::DELIVERY_MINUTES_FROM_KEY => $this->helper->getPart($from, 1),
                    QueueDataInterface::DELIVERY_HOURS_TO_KEY     => $this->helper->getPart($to, 0),
                    QueueDataInterface::DELIVERY_MINUTES_TO_KEY   => $this->helper->getPart($to, 1),
                ];

                $data['order']['delivery_info'] = array_merge($data['order']['delivery_info'], $timeData);

                $isModified = true;
            }

            if (isset($data['order']['delivery_info']['delivery_day'])
                && !$data['order']['delivery_info']['delivery_day'] instanceof \DateTimeInterface) {
                try {
                    $deliveryDay = $this->filterDate->filter($data['order']['delivery_info']['delivery_day']);
                    $data['order']['delivery_info']['delivery_day'] = $deliveryDay;

                    $isModified = true;
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }

            if ($isModified) {
                $request->setParams($data);
            }
        }
    }
}
