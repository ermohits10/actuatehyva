<?php


namespace Calculus\Catalog\Model;
use Magento\Framework\Data\Tree\Node;
//use Calculus\Catalog\Api\Data\CategoryTreeInterface2Factory;

class CategoryManagement 
{

     /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $categoryCollection;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory
     */
    protected $treeFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection
     * @param \Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory $treeFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection,
        \Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory $treeFactory
       // \Calculus\Catalog\Api\Data\CategoryTreeInterface2Factory $treeFactory

    ) {
        $this->categoryTree = $categoryTree;
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollection;
        $this->treeFactory = $treeFactory;
    
    }

    
    /**
     * @param \Magento\Catalog\Model\Category|null $category
     * @return Node|null
     */
    public function getRootNode($category = null)
    {
        if ($category !== null && $category->getId()) {
            return $this->getNode($category);
        }

        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        $tree = $this->categoryTree->load(null);
        $this->prepareCollection();
        $tree->addCollectionData($this->categoryCollection);
        $root = $tree->getNodeById($rootId);
        return $root;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return Node
     */
    protected function getNode(\Magento\Catalog\Model\Category $category)
    {
        $nodeId = $category->getId();
        $node = $this->categoryTree->loadNode($nodeId);
        $node->loadChildren();
        $this->prepareCollection();
        $this->categoryTree->addCollectionData($this->categoryCollection);
        return $node;
    }

  /**
     * @param \Magento\Catalog\Model\Category $category
     * @return Node
     */
    protected function getNodeById($nodeId)
    {
        $node = $this->categoryTree->loadNode($nodeId);
        $node->loadChildren();
        $this->prepareCollection();
        $this->categoryTree->addCollectionData($this->categoryCollection);
        return $node;
    }



    /**
     * @return void
     */
    protected function prepareCollection()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->categoryCollection->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'is_active'
        )->addAttributeToSelect(
            'ncompass_department_code'
        )->setProductStoreId(
            $storeId
        )->setLoadProductCount(
            true
        )->setStoreId(
            $storeId
        );
    }
 
    /**
     * @param \Magento\Framework\Data\Tree\Node $node
     * @param int $depth
     * @param int $currentLevel
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface
     */
    public function getTree($node, $depth = null, $currentLevel = 0)
    {
       

   if(is_int($node)){
       $node = $this->getNodeById($node);
   }else if(!$node){
    $store = $this->storeManager->getStore();
    $rootId = $store->getRootCategoryId();
    //mail("mlacey@findesolutions.com", $rootId, print_r( $store));
    $node = $this->getNodeById(2);  
   }

      /** @var \Magento\Catalog\Api\Data\CategoryTreeInterface[] $children */
      $children = $this->getChildren($node, $depth, $currentLevel);

      
        /** @var \Magento\Catalog\Api\Data\CategoryTreeInterface $tree */
        $tree = $this->treeFactory->create();

        $tree ->setId($node->getId())
            ->setParentId($node->getParentId())
            ->setName($node->getName())
            ->setPosition($node->getPosition())
            ->setLevel($node->getLevel())
            ->setIsActive($node->getIsActive())
            ->setProductCount($node->getProductCount())
            ->setNcompassDepartmentCode($node->getNcompassDepartmentCode())
            ->setChildrenData($children) 
         ;
        
        return $tree;
    }


    function treeFactory()
    {
    
    }


    /**
     * @param \Magento\Framework\Data\Tree\Node $node
     * @param int $depth
     * @param int $currentLevel
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]|[]
     */
    protected function getChildren($node, $depth, $currentLevel)
    {
        if ($node->hasChildren()) {
            $children = [];
            foreach ($node->getChildren() as $child) {
                if ($depth !== null && $depth <= $currentLevel) {
                    break;
                }
                $children[] = $this->getTree($child, $depth, $currentLevel + 1);
            }
            return $children;
        }
        return [];
    }


}
