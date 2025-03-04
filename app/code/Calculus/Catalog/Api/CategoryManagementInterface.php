<?php
namespace Calculus\Catalog\Api;
interface CategoryManagementInterface
{
   /**
     * Retrieve list of categories
     *
     * @param int $rootCategoryId
     * @param int $depth
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Calculus\Catalog\Api\Data\CategoryTreeInterface2 containing Tree objects
     */
    public function getTree($rootCategoryId = null, $depth = null);
}