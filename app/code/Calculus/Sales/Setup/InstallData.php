<?php
 
namespace Calculus\Sales\Setup;
 
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class InstallData implements InstallDataInterface
{
 
    protected $salesSetupFactory;
 
    /**
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory){
        $this->salesSetupFactory = $salesSetupFactory;
    }
 
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
 
        $salesSetup->addAttribute(Order::ENTITY, "ncompass_order_code", [
            'type'     => "varchar",
            'label'    => "NCompass Order Code",
            'input'    => "text",
            'system'   => 0,
            'user_defined' => true,
        ]);
 
        $setup->endSetup();
 
    }
}