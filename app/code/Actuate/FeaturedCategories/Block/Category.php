<?php

namespace Actuate\FeaturedCategories\Block;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;
class Category extends \Magento\Framework\View\Element\Template
{
    protected $_categoryCollectionFactory;
	protected $_categoryHelper;
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Helper\Category $categoryHelper,
		StoreManagerInterface $storeManager,
		array $data = []
	)
	{
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
		$this->_categoryHelper = $categoryHelper;
		parent::__construct($context, $data);
		$this->_storeManager = $storeManager;
	}
	/**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
	public function getCategoryCollection()	{
		$category_slider = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getValue('featuredcategories/general/slider',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,);

		if($category_slider == 1){
			$collection = $this->_categoryCollectionFactory->create();
	     	$collection->addAttributeToSelect('*')->addFieldToFilter('is_active', 1)->addAttributeToFilter('include_in_menu', 1)->addAttributeToFilter('is_featured', 1)->addAttributeToSort('position');
	        // print_r($collection->getData());die;
			return  $collection;
		}
    }

	public function getCategoryImage($id){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$category = $objectManager->create('Magento\Catalog\Model\Category')->load($id);
	}
	public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }
}