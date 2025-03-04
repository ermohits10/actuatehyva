<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model\Config\Source;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Module\Manager as ModuleManager;

class ProductHidePriceCustomerGroupSystem extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * GroupRepositoryInterface
     *
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * SearchCriteriaBuilder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * ModuleManager
     *
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * ProductHidePriceCustomerGroupSystem constructor.
     *
     * @param ModuleManager $moduleManager
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ModuleManager $moduleManager,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->moduleManager = $moduleManager;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get all options
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllOptions()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [];

        /** @var GroupInterface[] $groups */
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($groups->getItems() as $group) {
            $customerGroups[] = [
                'label' => $group->getCode(),
                'value' => $group->getId(),
            ];
        }

        $this->_options = $customerGroups;
        return $this->_options;
    }

    /**
     * Get option text
     *
     * @param int|string $value
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => __('Product Hide Price Customer Group  ') . $attributeCode . __(' column'),
            ],
        ];
    }
}
