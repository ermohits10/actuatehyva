<?php
declare(strict_types=1);
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
 * @package    Hyva_BssMegaMenu
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Hyva\BssMegaMenu\Block\Html;

use Bss\MegaMenu\Block\Html\Menu as BssMenu;
use Magento\Cms\Block\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

class Menu extends BssMenu
{
    const CONTENT_TYPE_CLASSIC = 1;
    const CONTENT_TYPE_CATEGORY_LISTING = 2;
    const CONTENT_TYPE_CONTENT = 3;

    /**
     * @var \Hyva\Theme\Service\CurrentTheme
     */
    protected $currentTheme;

    /**
     * @param \Hyva\Theme\Service\CurrentTheme $currentTheme
     * @param Template\Context $context
     * @param \Bss\MegaMenu\Helper\Data $helper
     * @param \Bss\MegaMenu\Model\Menu $menu
     * @param \Bss\MegaMenu\Model\ResourceModel\MenuItems\CollectionFactory $menuItemsCollection
     * @param \Magento\Theme\Block\Html\Topmenu $topMenuDefault
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Hyva\Theme\Service\CurrentTheme $currentTheme,
        Template\Context $context,
        \Bss\MegaMenu\Helper\Data $helper,
        \Bss\MegaMenu\Model\Menu $menu,
        \Bss\MegaMenu\Model\ResourceModel\MenuItems\CollectionFactory $menuItemsCollection,
        \Magento\Theme\Block\Html\Topmenu $topMenuDefault,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $helper, $menu, $menuItemsCollection, $topMenuDefault, $resource, $data);
        $this->currentTheme = $currentTheme;
    }

    /**
     * Get Mega Menu array data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMegaMenuData(): array
    {
        $storeId = $this->getStoreId();
        $collection = $this->menuItemsCollection->create();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('store_id', $storeId);
        $menu = null;

        if (!$collection->getSize()) {
            $storeId=0;
            $collection = $this->menuItemsCollection->create();
            $collection->addFieldToFilter('status', 1);
            $collection->addFieldToFilter('store_id', $storeId);
        }

        $new_arr = [];
        foreach ($collection->getData() as $arr) {
            $new_arr['j1_' . $arr['menu_id']] = $arr;
        }

        if ($this->getHelperData()->getMegaMenuConfig($storeId) !== null) {
            $menu = $this->helper->unserialize(
                $this->getHelperData()->getMegaMenuConfig($storeId)
            );
            if (!$menu) {
                $menu = $this->helper->unserialize(
                    $this->getHelperData()->getMegaMenuConfig()
                );
            }
        }

        if (isset($menu[0])) {
            $menu = $menu[0];
        }

        if (!isset($menu['children']) || empty($new_arr)) {
            return [];
        }

        return $this->remappingMenuData($menu['children'], $new_arr);
    }

    /**
     * Mapping raw mega menu data to correct form data
     *
     * @param array $treeMenuItems
     * @param array $collection
     * @param string $parentId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function remappingMenuData($treeMenuItems, $collection, $parentId = null): array
    {
        $finalItems = [];
        foreach ($treeMenuItems as $item) {
            if (!isset($collection[$item['id']])) {
                continue;
            }

            $tmpItem = $collection[$item['id']];
            $content = $this->helper->unserialize($tmpItem['content']);
            $finalItem = [
                'id' => $item['id'],
                'name' => __($item['text'])->getText(),
                'url' => $this->helper->getLinkUrl($tmpItem),
                'custom_css' => $content['custom_css'] ?? null,
                'label_html' => $this->getLabelColor($tmpItem, ''),
                'block_html' => [],
                'parent_id' => $parentId
            ];
            if ((int) $tmpItem['type'] === self::CONTENT_TYPE_CONTENT ||
                (int) $tmpItem['type'] === self::CONTENT_TYPE_CATEGORY_LISTING
            ) {
                $finalItem['block_html'] =  [
                    'top' => $this->getBlockTopHtml($tmpItem),
                    'right' => $this->getBlockRightHtml($tmpItem, true),
                    'bottom' => $this->getBlockBottomHtml($tmpItem, true),
                    'left' => $this->getBlockLeftHtml($tmpItem, true),
                    'center' => $this->getBlockCenterHtml($tmpItem, true)
                ];
            }

            if (isset($item['children']) && !empty($item['children'])) {
                $finalItem['children'] = $this->remappingMenuData($item['children'], $collection, $item['id']);
            } else {
                $finalItem['children'] = [];
            }
            $finalItems[] = $finalItem;
        }

        return $finalItems;
    }

    /**
     * Get mega menu html for desktop
     *
     * @param array $menus
     * @param array $collection
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getHtml($menus, $collection)
    {
        if (!$this->currentTheme->isHyva()) {
            return parent::_getHtml($menus, $collection);
        }

        $html = '';
        $childrenText = '';
        $i = 1;
        foreach ($menus as $menu) {
            $emptyHtml = '';
            if (!array_key_exists($menu['id'], $collection)) {
                continue;
            }
            $menu2 = $collection[$menu['id']];

            if (isset($menu['children'][0])) {
                $childrenText = 'parent';
            }

            if ($menu2['type'] == self::CONTENT_TYPE_CATEGORY_LISTING ||
                $menu2['type'] == self::CONTENT_TYPE_CONTENT
            ) {
                $childrenText .= ' bss-megamenu-fw';
            }

            //get content of mega menu
            $menu_content = isset($menu2['content']) ? $this->helper->unserialize($menu2['content']) : '';

            $customClass = $menu_content['custom_css'] ?? null;
            $html .= <<<HTML
                <div class="mr-2 level-0 $childrenText"
                 @mouseenter="setActiveHoverMenu('{$menu['id']}')"
                 @mouseleave="delete hoverPanelActiveId['{$menu['id']}']">
                    <span class="flex items-center block py-0.5 text-md">
                        <a class="nav-link w-full py-3 px-6 font-serif text-base leading-6 font-normal text-gray-900 hover:underline level-0 $customClass"
                            href="{$this->helper->getLinkUrl($menu2)}"
                            title="{$menu['text']}"
                        >
                            <span class="font-serif text-base leading-6 font-normal text-gray-900 hover:underline level-0 category-name">{$menu['text']}</span>
                            {$this->getLabelColor($menu2, '')}
                        </a>
                    </span>
                    {$this->_getContentHtml($menu, 0, $i, $collection, $menu2['type'], $emptyHtml)}
                </div>
            HTML;
            $i++;
        }
        return $html;
    }

    /**
     * Render html for default menu item
     *
     * @param array $menu
     * @param int $level
     * @param string $nav
     * @param array $collection
     * @param bool $hasChild
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getChildHtmlDefault($menu, $level, $nav, $collection, bool $hasChild = false)
    {
        if (!$this->currentTheme->isHyva()) {
            return parent::_getChildHtmlDefault($menu, $level, $nav, $collection);
        }

        $html = '';
        if (count($menu['children']) == 0) {
            return $html;
        }

        $countCollection = $this->countCollection($menu, $collection);

        if ($countCollection == 0) {
            return $html;
        }

        $hoverActiveId = $menu['id'];

        $childrendCss = $hasChild ? 'top-0 left-100per' : '';

        $html .= <<<HTML
            <div class="absolute z-10 shadow-lg bg-container-lighter bg-opacity-95 hidden $childrendCss"
                :class="{
                    'hidden': !hoverPanelActiveId?.$hoverActiveId,
                    'block': hoverPanelActiveId?.$hoverActiveId
                }">
        HTML;
        $i = 1;
        $level++;
        foreach ($menu['children'] as $childrens) {
            if (!array_key_exists($childrens['id'], $collection)) {
                continue;
            }

            $menu2 = $collection[$childrens['id']];
            $menu_content = isset($menu2['content']) ? $this->helper->unserialize($menu2['content']) : '';
            $customClass = $menu_content['custom_css'] ?? '';

            $hasChildren = isset($childrens['children'][0]);
            $mouseEvent = '';
            if ($hasChildren) {
                $mouseEvent = '@mouseenter="setActiveHoverMenu(\''. $childrens['id'] .'\')"';
                $mouseEvent .= ' @mouseleave="delete hoverPanelActiveId[\''. $childrens['id'] .'\']"';
            }
            $html .= <<<HTML
                <div class="relative level-$level parent px-3" $mouseEvent>
                <a class="nav-link block w-full px-6 py-4 whitespace-nowrap first:mt-0 $customClass"
                    href="{$this->helper->getLinkUrl($menu2)}">
                    <span class="text-base text-gray-700 hover:underline category-name">{$childrens['text']}</span>
                    {$this->getLabelColor($menu2, '')}
                </a>
            HTML;
            $nav_child = $nav . '-' . $i;
            if (isset($childrens['children'][0])) {
                $html .= $this->_getChildHtmlDefault($childrens, $level, $nav_child, $collection, $hasChildren);
            }
            $i++;
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Render html for Menu Content Type = `Category Listing`
     *
     * @param array|object $menu
     * @param array|object $collection
     * @return string
     * @throws LocalizedException
     */
    protected function _getChildHtmlCatagoryList($menu, $collection): string
    {
        if (!$this->currentTheme->isHyva()) {
            return parent::_getChildHtmlCatagoryList($menu, $collection);
        }

        $childNumber = $this->countCollection($menu, $collection);
        $menuData = $collection[$menu['id']];

        if ($childNumber == 0
            && $menuData['block_top'] == ''
            && $menuData['block_left'] == ''
            && $menuData['block_bottom'] == ''
            && $menuData['block_right'] == ''
        ) {
            return '';
        }

        return <<<HTML
            <div class="divide-y divide-gray-300/50 space-y-6 absolute z-10 shadow-lg bg-container-lighter bg-opacity-95 hidden left-0 w-full overflow-auto max-h-[500px] scrollbar" :class="{
                    'hidden': !hoverPanelActiveId?.{$menu['id']},
                    'block': hoverPanelActiveId?.{$menu['id']}
                }">
            <!-- Top -->
            {$this->getBlockTopHtml($menuData, '')}
            <!-- Main Content -->
            {$this->getBlockContentHtml($menuData, $menu, $collection)}
            <!-- Bottom -->
            {$this->getBlockBottomHtml($menuData)}
            </div>
        HTML;
    }

    /**
     * Render html for Menu Content Type = `content`
     *
     * @param array $menu
     * @param array $collection
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getChildHtmlContent($menu, $collection): string
    {
        if (!$this->currentTheme->isHyva()) {
            return parent::_getChildHtmlContent($menu, $collection);
        }

        $menu2 = $collection[$menu['id']];

        if ($menu2['block_top'] == ''
            && $menu2['block_left'] == ''
            && $menu2['block_bottom'] == ''
            && $menu2['block_right'] == ''
            && $menu2['block_content'] == ''
        ) {
            return '';
        }

        return <<<HTML
            <div class="divide-y divide-gray-300/50 space-y-6 absolute z-10 shadow-lg bg-container-lighter bg-opacity-95 hidden left-0 w-full overflow-auto max-h-[500px] scrollbar" :class="{
                    'hidden': !hoverPanelActiveId?.{$menu['id']},
                    'block': hoverPanelActiveId?.{$menu['id']}
                }">
            <!-- Top -->
            {$this->getBlockTopHtml($menu2)}
            <!-- Main Content -->
            {$this->getBlockContentHtml($menu2, $menu, $collection)}
            <!-- Bottom -->
            {$this->getBlockBottomHtml($menu2)}
            </div>
        HTML;
    }

    /**
     * Get content layout size
     *
     * @param array $menu
     * @return int
     */
    protected function getLayoutSize(array $menu): int
    {
        $size = 1;
        if ($menu['block_right'] != '') {
            $size++;
        }
        if ($menu['block_left'] != '') {
            $size++;
        }

        return $size;
    }

    /**
     * Render html for block content (block between top and bottom)
     *
     * @param array $menuData
     * @param array $menu
     * @param array $collection
     * @return string
     */
    protected function getBlockContentHtml($menuData, $menu, $collection): string
    {
        if ((int) $menuData['type'] === self::CONTENT_TYPE_CATEGORY_LISTING) {
            $childSize = $this->countCollection($menu, $collection);
            if ($childSize === 0 && empty($menuData['block_left']) && empty($menuData['block_right'])) {
                return '';
            }
            $centerBlockHtml = $this->renderBlockCenterForCategoryListing($menuData, $menu, $collection);
        } else {
            if (empty($menuData['block_content']) && empty($menuData['block_left']) && empty($menuData['block_right'])) {
                return '';
            }


            $centerBlockHtml = $this->getBlockCenterHtml($menuData);

        }

        $class = 'pt-6';
        if (empty($menu['block_top'])) {
            $class = '';
        }

        return <<<HTML
            <div class="block-content flex justify-center $class">
                {$this->getBlockLeftHtml($menuData)}
                {$centerBlockHtml}
                {$this->getBlockRightHtml($menuData)}
            </div>
        HTML;

    }

    /**
     * Render html for block left (left of content block)
     *
     * @param array $menu
     * @param bool $isMobile
     * @return string
     * @throws LocalizedException
     */
    protected function getBlockLeftHtml($menu, bool $isMobile = false): string
    {
        if (empty($menu['block_left'])) {
            return '';
        }

        $blockId = $menu['block_left'];
        $layoutSize = $this->getLayoutSize($menu);
        $class = '';
        if ($layoutSize === 2) {
            $class = 'w-1/2';
        } else if ($layoutSize === 3) {
            $class = 'w-1/3';
        }

        if ($isMobile) {
            $class = 'w-full';
        }
        return <<<HTML
            <div class="block-left $class">
                {$this->getLayout()->createBlock(Block::class)->setBlockId($blockId)->toHtml()}
            </div>
        HTML;
    }

    /**
     * Render html for block right (right of content block)
     *
     * @param array $menu
     * @param bool $isMobile
     * @return string
     * @throws LocalizedException
     */
    protected function getBlockRightHtml($menu, bool $isMobile = false): string
    {
        if (empty($menu['block_right'])) {
            return '';
        }
        $blockId = $menu['block_right'];
        $layoutSize = $this->getLayoutSize($menu);
        $class = '';
        if ($layoutSize === 2) {
            $class = 'w-1/2';
        } else if ($layoutSize === 3) {
            $class = 'w-1/3';
        }

        if ($isMobile) {
            $class = 'w-full';
        }
        return <<<HTML
            <div class="block-right $class">
                {$this->getLayout()->createBlock(Block::class)->setBlockId($blockId)->toHtml()}
            </div>
        HTML;
    }

    /**
     * Render html for block top
     *
     * @param array $menu
     * @return string
     * @throws LocalizedException
     */
    protected function getBlockTopHtml($menu): string
    {
        if (empty($menu['block_top'])) {
            return '';
        }

        $html = $this->getLayout()
            ->createBlock(Block::class)
            ->setBlockId($menu['block_top'])
            ->toHtml();

        return <<<HTML
            <div class="block-top">
                $html
            </div>
        HTML;

    }

    /**
     * Render html for block bottom
     *
     * @param array $menu
     * @param bool $isMobile
     * @return string
     * @throws LocalizedException
     */
    protected function getBlockBottomHtml($menu, bool $isMobile = false): string
    {
        if (empty($menu['block_bottom'])) {
            return '';
        }
        $html = $this->getLayout()
            ->createBlock(Block::class)
            ->setBlockId($menu['block_bottom'])
            ->toHtml();

        $paddingCss = 'pt-6';
        if ($isMobile ||
            (empty($menu['block_top']) && empty($menu['block_content'])) ||
            ((int) $menu['type'] === self::CONTENT_TYPE_CATEGORY_LISTING && empty($menu['children']))
        ) {
            $paddingCss = '';
        }
        return <<<HTML
            <div class="block-bottom $paddingCss">
                $html
            </div>
        HTML;

    }

    /**
     * Render html for block center of content block (between left and right)
     *
     * @param array $menu
     * @param bool $isMobile
     * @return string
     * @throws LocalizedException
     */
    protected function getBlockCenterHtml($menu, bool $isMobile = false): string
    {
        $class = 'w-1/2';
        if (!empty($menu['block_left']) && !empty($menu['block_right'])) {
            $class = 'w-1/3';
        } else if (empty($menu['block_left']) && empty($menu['block_right'])) {
            $class = 'w-full';
        }

        if ($isMobile) {
            $class = 'w-full';
        }
        $centerBlockHtml = $this->getLayout()
            ->createBlock(Block::class)
            ->setBlockId($menu['block_content'])
            ->toHtml();
        return <<<HTML
            <div class="block-center $class">$centerBlockHtml</div>
        HTML;
    }

    /**
     * Render content block for menu node type category listing
     *
     * @param array $currentMenuData
     * @param array $menu
     * @param array $collection
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function renderBlockCenterForCategoryListing($currentMenuData, $menu, $collection): string
    {
        $layoutSize = $this->getLayoutSize($currentMenuData);
        $class = 'mr-3 ml-3 space-y-6 ';
        if ($layoutSize === 2) {
            $class .= 'w-1/2';
        } else if ($layoutSize === 3) {
            $class .= 'w-1/3';
        } else {
            $class .= 'w-full';
        }

        $html = '';
        foreach ($menu['children'] as $childrens) {
            if (!array_key_exists($childrens['id'], $collection)) {
                continue;
            }
            //get content of mega menu
            $menuData = $collection[$childrens['id']];

            $menu_content = isset($menuData['content']) ? $this->helper->unserialize($menuData['content']) : '';

            $customCss = $menu_content['custom_css'] ?? null;
            $html .= '<div>
                        <h3 class="border-b pb-1" title="' . __($childrens['text']) . '">
                            <a href="' . $this->helper->getLinkUrl($collection[$childrens['id']]) . '" '
                . 'class="' . $customCss . '" >'
                . '<span class="text-base text-gray-700 hover:underline category-name mr-2">'
                . __($childrens['text'])
                . '</span>';
            $html .= $this->getLabelColor($menuData, '');
            $html .= '</a></h3>';

            if (isset($childrens['children'][0])) {
                $html .= '<ul class="ml-5 mt-2">';
                foreach ($childrens['children'] as $child) {
                    if (!array_key_exists($child['id'], $collection)) {
                        continue;
                    }
                    //get content of mega menu
                    $menuData = $collection[$child['id']];
                    $menu_content = isset($menuData['content']) ? $this->helper->unserialize($menuData['content']) : '';
                    $childCustomCss = $menu_content['custom_css'] ?? null;
                    $html .= '<li><a href="' . $this->helper->getLinkUrl($collection[$child['id']]) . '" class="'
                        . $childCustomCss . '" '
                        . '><span class="text-base text-gray-700 hover:underline category-name mr-2">' . __($child['text']) . '</span>';
                    $html .= $this->getLabelColor($menuData, '');
                    $html .= '</a></li>';
                }
                $html .= '</ul>';
            }

            $html .= '</div>';
        }
        return <<<HTML
            <div class="block-center $class">
                $html
            </div>
        HTML;
    }

    /**
     * Render level 2 menu for mobile
     *
     * @param array $item
     * @param int $level
     * @param null|string $parentId
     * @return string
     */
    public function renderMenuChildrenMobile(array $item, int $level = 1, string $parentId = null): string
    {
        $seeChildMenuIndicatorHtml = '';
        $childMenuHtml = '';
        $mlLevel = $level + 2;
        $itemId = $item["id"];

        if (!empty($item['children'])) {
            $nextChildMenuHtml = '';
            foreach ($item['children'] as $child) {
                $nextChildMenuHtml .= $this->renderMenuChildrenMobile($child, $level + 1, $item["id"]);
            }
            $childMenuHtml = <<<HTML
                <div
                    class="absolute top-0 right-0 z-10 w-full h-auto transition-transform duration-200 ease-in-out
                         transform bg-container-lighter translate-x-full"
                         :class="{
                            'translate-x-0': mobilePanelActiveId !== '$parentId' && mobilePanelActiveId !== '$itemId'
                         }"
                >
                    <a class="flex items-center px-8 py-4 border-b cursor-pointer bg-container border-container"
                       @click="removeActiveId('$itemId'); mobilePanelActiveId = '$parentId'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="24"
                             width="24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="ml-$mlLevel">{$this->escapeHtml($item['name'])}</span>
                    </a>
                    <a href="{$this->escapeUrl($item['url'])}"
                       title="{$this->escapeHtmlAttr($item['name'])}"
                       class="flex items-center w-full px-8 py-4 border-b cursor-pointer
                    bg-container-lighter border-container hover:bg-container-darker hover:underline"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" height="24"
                             width="24"></svg>
                        <span class="ml-4">{$this->escapeHtml(__('View All'))}</span>
                    </a>
                    $nextChildMenuHtml
                </div>
            HTML;

            $handleNextMenuParams = sprintf("'%s'", $item['id']);
            if ($parentId) {
                $handleNextMenuParams .= sprintf(", '%s'", $parentId);
            }
            $seeChildMenuIndicatorHtml .= <<<HTML
                <a class="absolute right-0 flex w-8 h-8 mr-8 border rounded cursor-pointer
                    bg-container-lighter border-container hover:bg-container hover:border-container"
                           @click="handleNextMenu($handleNextMenuParams)"
                        >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="24" width="24"
                         stroke="currentColor"
                         class="w-full h-full p-1"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            HTML;

        }

        return <<<HTML
            <div class="level-$level">
                <span
                    class="flex items-center transition-transform duration-150 ease-in-out transform"
                    :class="{
                        '-translate-x-full': mobilePanelActiveId === '$itemId'
                    }"
                >
                    <a class="flex items-center w-full px-8 py-4 border-b cursor-pointer bg-container-lighter border-container hover:bg-container-darker level-0"
                       href="{$this->escapeUrl($item['url'])}"
                       title="{$this->escapeHtmlAttr($item['name'])}">
                       <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" height="24"
                         width="24"></svg>
                        <span class="mr-2 hover:underline category-name ml-$mlLevel text-base text-gray-700 lg:ml-0">{$this->escapeHtml($item['name'])}</span>
                        {$item['label_html']}
                    </a>
                    $seeChildMenuIndicatorHtml
                </span>
                $childMenuHtml
            </div>
        HTML;
    }
}