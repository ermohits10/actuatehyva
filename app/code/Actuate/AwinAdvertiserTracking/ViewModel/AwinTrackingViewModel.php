<?php

namespace Actuate\AwinAdvertiserTracking\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Scommerce\GoogleTagManagerPro\Model\Entity\Attribute\Source\Categories;

class AwinTrackingViewModel implements ArgumentInterface
{
    private Categories $categories;

    /**
     * @param Categories $categories
     */
    public function __construct(
        Categories $categories
    ) {
        $this->categories = $categories;
    }

    /**
     * @param $id
     * @return mixed|null
     */
    public function getPrimaryCategoryById($id)
    {
        try {
            $categories = $this->categories->getCategories();
            $matchedCategory = array_filter($categories, function($category) use ($id) {
                return (int) $category['value'] === (int) $id;
            });
            if (!empty($matchedCategory)) {
                $matchedCategory = array_values($matchedCategory);
                return $matchedCategory[0]['label'] ?? null;
            }
        } catch (\Exception $e) {
        }
        return null;
    }
}
