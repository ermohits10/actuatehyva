<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DeliveryDate\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CustomerGroup extends Column
{
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var string
     */
    protected $customerGroupKey;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var array
     */
    protected $allGroups;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     * @param string $customerGroupKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        Escaper $escaper,
        array $components = [],
        array $data = [],
        $customerGroupKey = 'customer_group_ids'
    ) {
        $this->escaper                = $escaper;
        $this->customerGroupKey       = $customerGroupKey;
        $this->groupCollectionFactory = $groupCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareItem(array $item)
    {
        $content            = '';
        $origCustomerGroups = isset($item[$this->customerGroupKey]) ? $item[$this->customerGroupKey] : null;

        if ($origCustomerGroups === null || $origCustomerGroups === '' || $origCustomerGroups === []) {
            return __('All Groups');
        }

        if (!is_array($origCustomerGroups)) {
            $origCustomerGroups = [$origCustomerGroups];
        }

        /** @var \Magento\Customer\Model\Group[] $customerGroups */
        $customerGroups = $this->getAllCustomerGroups();
        foreach ($origCustomerGroups as $group) {
            if (isset($customerGroups[$group])) {
                $groupEntity = $customerGroups[$group];
                $groupLabel  = $groupEntity->getCustomerGroupCode() ? $groupEntity->getCustomerGroupCode(
                ) : $groupEntity->getId();
                $content     .= '- ' . $this->escaper->escapeHtml($groupLabel) . '<br/>';
            } else {
                $content .= '- ' . __('Unknown group: %1', $group) . '<br/>';
            }
        }

        return $content;
    }

    /**
     * @return array|\Magento\Customer\Api\Data\GroupInterface|\Magento\Customer\Model\Group[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllCustomerGroups()
    {
        if (empty($this->allGroups)) {
            $collection = $this->groupCollectionFactory->create();
            foreach ($collection->getItems() as $item) {
                $this->allGroups[$item->getId()] = $item;
            }
        }

        return $this->allGroups;
    }
}
