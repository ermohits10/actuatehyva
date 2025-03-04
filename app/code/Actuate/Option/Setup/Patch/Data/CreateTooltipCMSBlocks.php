<?php

namespace Actuate\Option\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

class CreateTooltipCMSBlocks implements DataPatchInterface
{
    /**
     * @var BlockFactory
     */
    protected BlockFactory $blockFactory;

    /**
     * constructor.
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        foreach ($this->getBlockContent() as $block) {
            $cmsData = [
                'title' => $block['title'],
                'identifier' => $block['identifier'],
                'content' => $block['content'],
                'stores' => [0],
                'is_active' => 1,
            ];
            $collectionTitleBlock = $this->blockFactory
                ->create()
                ->load($cmsData['identifier'], 'identifier');

            if (!$collectionTitleBlock->getId()) {
                $collectionTitleBlock->setData($cmsData)->save();
            } else {
                $collectionTitleBlock->setContent($cmsData['content'])->save();
            }
        }
    }

    /**
     * @return \string[][]
     */
    private function getBlockContent(): array
    {
        $disposalContent = <<<HTML
<style>#html-body [data-pb-style=XO61AIN]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="XO61AIN"><div data-content-type="text" data-appearance="default" data-element="main"><p>We can remove and recycle your old appliance for you when your new appliance is delivered, meaning you won't have to worry about what to do with it. You will need to do the following depending on the appliance:</p>
<ul>
<li>Fridge/Freezer: Unplug and fully defrost the product, removing any food.</li>
<li>Washer/Dryer: Unplug and disconnect from your water supply, drain the appliance and remove any clothing.</li>
<li>Dishwasher: Unplug and disconnect from your water supply. Please fully empty the dishwasher of items, ready for removal.</li>
</ul></div></div></div>
HTML;
        $installationContent = <<<HTML
<style>#html-body [data-pb-style=OBNAHT4]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="OBNAHT4"><div data-content-type="text" data-appearance="default" data-element="main"><p>We will ensure that your new appliance is fully installed and tested for full functionality upon delivery (except for fridges/freezers, which must be left unplugged for a minimum of 4-6hrs as per manufacturer guidelines).</p>
<ul>
<li>Please unplug, disconnect and move the appliance out of the space for the new machine.<br>Please note, this service can only be completed if a suitable power outlet and any relevant water connections are available within 1m of the desired location.<br>All integrated appliances are not eligible for the Set Up and connect service.<br>Our installation engineers will place your item(s) in situ (where possible).</li>
</ul></div></div></div>
HTML;

        $unpackContent = <<<HTML
<style>#html-body [data-pb-style=I2I9Q5J]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="I2I9Q5J"><div data-content-type="text" data-appearance="default" data-element="main"><p>Your item(s) will be taken to a ground floor room of your choice, unpacked and any subsequent rubbish will be taken away.</p>
<p>Please note Washing machines , Tumble Dryers, Dishwashers, Undercounter Fridges &amp; Freezers will be placed in situ after unpacking, but not connected. If you require this to be done please use our unpack, set up and connect service.</p>
<p>Our Couriers use 3rd parties to deliver to certain postcodes. When this occurs, installation, unfortunately, cannot be provided. This affects the following postcodes. If in doubt, please call us to check.</p>
<ul>
<li>BT 1-17, BT 18-94, G84, GY, HS, IM, IV17, IV21 -28, IV40 – 49, IV51 – 56, IV63, IV99, JE, KA27, KW, PA20-80, PH33-50, PO30-41, TR21-25, ZE, EIRE</li>
</ul></div></div></div>
HTML;

        $doorRemovalContent = <<<HTML
<style>#html-body [data-pb-style=KP9ORYR]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="KP9ORYR"><div data-content-type="text" data-appearance="default" data-element="main"><p>Our courier service can remove and re-install a front door should it be required for larger items such as American Fridge Freezers</p>
<p>Please note, the item will only be brought into the property, should you require unpack and set up then please add the additional services to your order.</p></div></div></div>
HTML;

        return [
            ['identifier' => 'disposal-tooltip', 'title' => 'Disposal Tooltip', 'content' => $disposalContent],
            ['identifier' => 'installation-tooltip', 'title' => 'Installation Tooltip', 'content' => $installationContent],
            ['identifier' => 'unpack-tooltip', 'title' => 'Unpack Tooltip', 'content' => $unpackContent],
            ['identifier' => 'door-removal-tooltip', 'title' => 'Door Removal Tooltip', 'content' => $doorRemovalContent],
        ];
    }
}
