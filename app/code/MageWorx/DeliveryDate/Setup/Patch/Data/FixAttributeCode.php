<?php
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FixAttributeCode implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        EavSetupFactory          $eavSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup           = $setup;
    }

    /**
     * Patch dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - then under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return $this
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

        $oldAttributeId = \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_TO . ' ';
        $attributeToOld = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $oldAttributeId
        );
        if ($attributeToOld) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $oldAttributeId,
                'attribute_code',
                \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_TO
            );
        }

        return $this;
    }
}
