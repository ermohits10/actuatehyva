<?php
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use MageWorx\DeliveryDate\Api\DeliveryOptionInterfaceFactory;
use MageWorx\DeliveryDate\Api\Repository\DeliveryOptionRepositoryInterface;
use Psr\Log\LoggerInterface;
use MageWorx\DeliveryDate\Helper\Data as Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AddDefaultDeliveryOption implements DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var DeliveryOptionInterfaceFactory
     */
    private $deliveryOptionInterfaceFactory;

    /**
     * @var DeliveryOptionRepositoryInterface
     */
    private $deliveryOptionRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigInterface
     */
    private $configResource;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * UpgradeData constructor.
     *
     * @param DeliveryOptionInterfaceFactory $deliveryOptionInterfaceFactory
     * @param DeliveryOptionRepositoryInterface $deliveryOptionRepository
     * @param LoggerInterface $logger
     * @param ConfigInterface $configResource
     * @param Helper $helper
     */
    public function __construct(
        DeliveryOptionInterfaceFactory $deliveryOptionInterfaceFactory,
        DeliveryOptionRepositoryInterface $deliveryOptionRepository,
        LoggerInterface $logger,
        ConfigInterface $configResource,
        Helper $helper
    ) {
        $this->deliveryOptionInterfaceFactory = $deliveryOptionInterfaceFactory;
        $this->deliveryOptionRepository       = $deliveryOptionRepository;
        $this->logger                         = $logger;
        $this->configResource                 = $configResource;
        $this->helper                         = $helper;
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
        if ($this->helper->getDefaultDeliveryOptionId()) {
            return $this; // already exists
        }

        /** @var \MageWorx\DeliveryDate\Api\DeliveryOptionInterface $deliveryOption */
        $deliveryOption = $this->deliveryOptionInterfaceFactory->create();
        $deliveryOption->setName('Default Delivery Configuration');
        $deliveryOption->setIsActive(false);
        $deliveryOption->setStoreIds([Store::DEFAULT_STORE_ID]);
        $deliveryOption->setStartDaysLimit(0);
        $deliveryOption->setFutureDaysLimit(90);
        $deliveryOption->setSortOrder(1);

        try {
            $this->deliveryOptionRepository->save($deliveryOption);
            $this->configResource->saveConfig(
                Helper::XML_PATH_DEFAULT_DELIVERY_OPTION_ID,
                $deliveryOption->getId(),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e);
        }

        return $this;
    }

    /**
     * Rollback all changes, done by this patch
     *
     * @return void
     */
    public function revert()
    {
        $defaultDeliveryOptionId = $this->helper->getDefaultDeliveryOptionId();
        if (!$defaultDeliveryOptionId) {
            return;
        }

        try {
            $this->deliveryOptionRepository->deleteById($defaultDeliveryOptionId);
            $this->configResource->saveConfig(
                Helper::XML_PATH_DEFAULT_DELIVERY_OPTION_ID,
                0,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e);
        }
    }
}
