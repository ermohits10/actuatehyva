<?php

declare(strict_types=1);

namespace Actuate\MirasvitSeoFilter\Plugin\Backend;

use Actuate\MirasvitSeoFilter\ViewModel\FilterOptionViewModel;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class SaveAttributeSavePlugin
{
    private FilterOptionViewModel $filterOptionViewModel;

    /**
     * @param FilterOptionViewModel $filterOptionViewModel
     */
    public function __construct(FilterOptionViewModel $filterOptionViewModel)
    {
        $this->filterOptionViewModel = $filterOptionViewModel;
    }

    /**
     * @param Attribute $subject
     */
    public function beforeSave(Attribute $subject)
    {
        $attributeCode = (string)$subject->getAttributeCode();
        if ($attributeCode) {
            $seoFilterData = $subject->getData('seo_filter');
            $existData = $this->filterOptionViewModel->getExistsOptions($attributeCode);

            if (isset($seoFilterData['indexable']) && !empty($seoFilterData['indexable'])) {
                $connection = $this->filterOptionViewModel->getConnection();
                $allOptions = array_keys($seoFilterData['options']);

                if (!empty($allOptions)) {
                    foreach ($allOptions as $optionId) {
                        if (!isset($existData[$optionId])) {
                            $data = [
                                'attribute_id' => $subject->getAttributeId(),
                                'attribute_code' => $attributeCode,
                                'option' => $optionId,
                                'is_indexable' => $seoFilterData['indexable'][$optionId] ?? '0',
                            ];
                            $connection->insert(
                                $connection->getTableName('actuate_seo_filter_indexable'),
                                $data
                            );
                        } else {
                            $connection->update(
                                $connection->getTableName('actuate_seo_filter_indexable'),
                                ['is_indexable' => isset($seoFilterData['indexable'][$optionId]) ? 1 : 0 ],
                                ['attribute_code = ?' => $attributeCode, 'option in (?)' => $optionId]
                            );
                        }
                    }
                }
            } elseif (isset($seoFilterData['category_ids']) && !empty($seoFilterData['category_ids'])) {
                $connection = $this->filterOptionViewModel->getConnection();
                $categoryIds = explode(',', $seoFilterData['category_ids']);

                if (!empty($existData)) {
                    $deleteCategories = array_diff(array_keys($existData), $categoryIds);
                    if (!empty($deleteCategories)) {
                        $connection->delete(
                            $connection->getTableName('actuate_seo_filter_indexable'),
                            ['attribute_code = ?' => $attributeCode, 'option IN (?)' => $deleteCategories]
                        );
                    }
                }
                foreach ($categoryIds as $categoryId) {
                    if (!isset($existData[$categoryId])) {
                        $data = [
                            'attribute_id' => $subject->getAttributeId(),
                            'attribute_code' => $attributeCode,
                            'option' => $categoryId,
                            'is_indexable' => 1,
                        ];
                        $connection->insert(
                            $connection->getTableName('actuate_seo_filter_indexable'),
                            $data
                        );
                    }
                }
            }
        }
    }
}
