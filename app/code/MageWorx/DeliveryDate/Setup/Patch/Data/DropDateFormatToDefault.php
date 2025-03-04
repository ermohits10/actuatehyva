<?php
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class DropDateFormatToDefault implements DataPatchInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
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
        $connection = $this->setup->getConnection();
        $connection->delete(
            $this->setup->getTable('core_config_data'),
            'path = \'delivery_date/visualisation/date_format_custom\''
        );
        $connection->delete(
            $this->setup->getTable('core_config_data'),
            'path = \'delivery_date/visualisation/date_format\''
        );

        return $this;
    }

    /**
     * This version associate patch with Magento setup version.
     * For example, if Magento current setup version is 2.0.3 and patch version is 2.0.2 then
     * this patch will be added to registry, but will not be applied, because it is already applied
     * by old mechanism of UpgradeData.php script
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.12.0';
    }
}
