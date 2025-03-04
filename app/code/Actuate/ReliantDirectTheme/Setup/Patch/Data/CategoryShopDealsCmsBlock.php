<?php

namespace Actuate\ReliantDirectTheme\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Store\Model\Store;

class CategoryShopDealsCmsBlock implements DataPatchInterface
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
<style>#html-body [data-pb-style=BBG71YV],#html-body [data-pb-style=P9O47L8]{background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=P9O47L8]{justify-content:flex-start;display:flex;flex-direction:column;background-position:center center}#html-body [data-pb-style=BBG71YV]{background-position:left top;align-self:stretch}#html-body [data-pb-style=COPP43J]{display:flex;width:100%}#html-body [data-pb-style=T72R1EF]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{\&quot;desktop_image\&quot;:\&quot;{{media url=wysiwyg/cms-bg.jpg}}\&quot;,\&quot;mobile_image\&quot;:\&quot;{{media url=wysiwyg/Group_43.png}}\&quot;}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="P9O47L8"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="BBG71YV"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="COPP43J"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="T72R1EF"><div data-content-type="text" data-appearance="default" data-element="main"><h2 id="GSQW395" style="text-align: center;"><span style="color: #ffffff;">See our latest deals <strong>Shop Now</strong></span></h2></div></div></div></div></div></div>
CMS;
        $this->moduleDataSetup->startSetup();

        $block = $this->blockFactory->create()
            ->setTitle('Category Shop Deals')
            ->setIdentifier('category-shopdeals')
            ->setIsActive(true)
            ->setContent($content)
            ->setStores([Store::DEFAULT_STORE_ID]);
        $this->blockRepository->save($block);

        $this->moduleDataSetup->endSetup();
    }
}
