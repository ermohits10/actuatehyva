<?php

namespace Actuate\RelevantProducts\ViewModel;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RelevantProducts implements ArgumentInterface
{
    private CollectionFactory $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $product
     * @return array
     */
    public function getRelevantProductCollection($product): array
    {
        $relevantProducts = [];
        $relevantProductSkus = $product->getData('grouped_skus');

        if ($relevantProductSkus) {
            $relevantProductSkuList = array_map('trim', explode(',', $relevantProductSkus));
            $productCollection = $this->collectionFactory->create()
                ->addAttributeToFilter('sku', ['in' => $relevantProductSkuList])
                ->addAttributeToFilter('status', ['eq' => 1]);
            if (!empty($product->getAttributeText('nc1000000276'))) {
                $productCollection->addAttributeToSelect('nc1000000276');
            } elseif (!empty($product->getAttributeText('nc1000000828'))) {
                $productCollection->addAttributeToSelect('nc1000000828');
            }
            $productCollection->addAttributeToSelect('sku')
                ->addUrlRewrite();

            if ($productCollection->getSize() > 0) {
                foreach ($productCollection as $relevantProduct) {
                    $relevantProductInfo = [
                        'id' => $relevantProduct->getId(),
                        'productUrl' => $relevantProduct->getProductUrl()
                    ];
                    if (!empty($product->getAttributeText('nc1000000276'))) {
                        $relevantProductInfo['size'] = $relevantProduct->getAttributeText('nc1000000276');
                    } elseif (!empty($product->getAttributeText('nc1000000828'))) {
                        $relevantProductInfo['size'] = $relevantProduct->getAttributeText('nc1000000828');
                    }
                    $relevantProducts[$relevantProduct->getSku()] = $relevantProductInfo;
                }
            }
            $relevantProducts = $this->sortBySkus($relevantProducts, $relevantProductSkuList);
        }

        return $relevantProducts;
    }

    /**
     * @param $relevantProducts
     * @param $relevantProductSkuList
     * @return array
     */
    private function sortBySkus($relevantProducts, $relevantProductSkuList): array
    {
        $sortedRelevantProducts = [];
        foreach ($relevantProductSkuList as $relevantProductSku) {
            if (isset($relevantProducts[$relevantProductSku])) {
                $sortedRelevantProducts[$relevantProductSku] = $relevantProducts[$relevantProductSku];
            }
        }
        return $sortedRelevantProducts;
    }
}
