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
namespace Bss\HidePrice\Plugin\Bundle\Block;

/**
 * Class Bundle
 *
 * @package Bss\HidePrice\Plugin\Bundle\Block
 */
class Bundle
{
    /**
     * Helper
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Bundle constructor.
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->json = $json;
    }

    /**
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterGetJsonConfig(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject,
        $result
    ) {
        $result = $this->json->unserialize($result);
        $result['hidePrice'] = [];
        $result['hideCart'] = [];
        if ($this->helper->isEnable()) {
            $arrOptions = $result['options'];
            $parentProduct = $subject->getProduct();
            $activeHidePrice = $this->helper->activeHidePrice($parentProduct);
            $hidePriceActionActive = $this->helper->hidePriceActionActive($parentProduct);
            $hidePriceOptions = [];
            $hideCartOptions = [];
            foreach ($arrOptions as $keyArrOption => $options) {
                foreach ($options as $selection => $option) {
                    if ($selection == 'selections') {
                        foreach ($option as $keyOption => $data) {
                            if ($activeHidePrice && $hidePriceActionActive != 2) {
                                $priceOption = $arrOptions[$keyArrOption][$selection][$keyOption]['prices'];
                                $priceOption['oldPrice']['amount'] = 0;
                                $priceOption['oldPrice']['amount'] = 0;
                                $priceOption['finalPrice']['amount'] = 0;
                                $arrOptions[$keyArrOption][$selection][$keyOption]['prices'] = $priceOption;
                                $hidePriceOptions[$keyArrOption] = $keyOption;
                                $hideCartOptions[] = $keyOption;
                            } else {
                                $childId = $arrOptions[$keyArrOption][$selection][$keyOption]['optionId'];
                                $child = $this->productRepository->getById($childId);
                                if ($this->helper->activeHidePrice($child)) {
                                    if ($this->helper->hidePriceActionActive($child) != 2) {
                                        $priceOption = $arrOptions[$keyArrOption][$selection][$keyOption]['prices'];
                                        $priceOption['oldPrice']['amount'] = 0;
                                        $priceOption['oldPrice']['amount'] = 0;
                                        $priceOption['finalPrice']['amount'] = 0;
                                        $arrOptions[$keyArrOption][$selection][$keyOption]['prices'] = $priceOption;
                                        $hidePriceOptions[$keyArrOption] = $keyOption;
                                    }
                                    $hideCartOptions[] = $keyOption;
                                }
                            }
                        }
                    }
                }
            }
            $result['options'] = $arrOptions;
            $result['hidePrice'] = $hidePriceOptions;
            $result['hideCart'] = $hideCartOptions;
        }
        $result = $this->json->serialize($result);
        return $result;
    }
}
