<?php

namespace Actuate\ReliantDirectTheme\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Store\Model\Store;

class BasketDeliveryInfoCmsBlock implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private BlockFactory $blockFactory;
    private BlockRepository $blockRepository;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        BlockRepository $blockRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     */
    public function apply()
    {
        $content = <<<CMS
<style>#html-body [data-pb-style=N5CXFUM]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="N5CXFUM"><div data-content-type="text" data-appearance="default" data-element="main"><p>FREE Delivery is available for all TVs, audio items and office furniture. Laundry products and the majority of refrigeration items incur a delivery fee of £19.99, with American fridge freezers incurring a delivery fee of £29.99. Delivery to Highlands, Islands and Northern Ireland will incur an additional fee between £49.99 to £89.99.</p></div></div></div>
CMS;
        $this->moduleDataSetup->startSetup();

        $block = $this->blockFactory->create()
            ->setTitle('Basket Page - Delivery Info')
            ->setIdentifier('basket-delivery-info')
            ->setIsActive(true)
            ->setContent($content)
            ->setStores([Store::DEFAULT_STORE_ID]);
        $this->blockRepository->save($block);

        $this->moduleDataSetup->endSetup();
    }
}
