<?php

namespace Actuate\RedirectSimpleProducts\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Predispatch implements ObserverInterface
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_productTypeConfigurable;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    private \Magento\Framework\App\ResponseFactory $responseFactory;

    private ResourceConnection $resourceConnection;

    /**
     * Predispatch constructor.
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->_productTypeConfigurable = $productTypeConfigurable;
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->responseFactory = $responseFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $pathInfo = $observer->getEvent()->getRequest()->getPathInfo();
        if ($observer->getRequest()->getFullActionName() === 'checkout_cart_configure') {
            return;
        }

        //Check URL with product URL key and trying to fetch product ID from
        $simpleProductId = null;
        if (str_contains($observer->getRequest()->getFullActionName(), 'noroute')) {
            $pathInfo = str_replace('.html', '', $pathInfo);
            $pathInfo = trim($pathInfo, '/');
            $simpleProductId = $this->checkUrlWithUrlKey($pathInfo);
        }

        // Getting simple product ID from request parameter
        if (!$simpleProductId) {
            $request = $observer->getEvent()->getRequest();
            $simpleProductId = $request->getParam('id');
        }

        if ($simpleProductId && strpos($pathInfo, 'product') === false) {
            return;
        }

        if (!$simpleProductId) {
            return;
        }

        $simpleProduct = $this->_productRepository->getById($simpleProductId, false, $this->_storeManager->getStore()->getId());
        if (!$simpleProduct || $simpleProduct->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            return;
        }

        $configProductId = $this->_productTypeConfigurable->getParentIdsByChild($simpleProductId);
        if (isset($configProductId[0])) {
            $configProduct = $this->_productRepository->getById($configProductId[0], false, $this->_storeManager->getStore()->getId());
            $configType = $configProduct->getTypeInstance();
            $attributes = $configType->getConfigurableAttributesAsArray($configProduct);

            $options = [];
            foreach ($attributes as $attribute) {
                $id = $attribute['attribute_id'];
                $value = $simpleProduct->getData($attribute['attribute_code']);
                $options[$id] = $value;
            }

            $options = http_build_query($options);
            $hash = $options ? '#' . $options : '';
            $configProductUrl = $configProduct->getUrlModel()
                    ->getUrl($configProduct) . $hash;
            $this->responseFactory->create()->setRedirect($configProductUrl, 301)->sendResponse();
            exit(0);
        }
    }

    /**
     * @param $url
     * @return mixed|null
     */
    private function checkUrlWithUrlKey($url)
    {
        $connection = $this->resourceConnection->getConnection();
        $sql = $connection->select()
            ->from(['ea' => $this->resourceConnection->getTableName('eav_attribute')], ['ea.attribute_id'])
            ->joinInner(
                ['cpev' => $this->resourceConnection->getTableName('catalog_product_entity_varchar')],
                'ea.attribute_id = cpev.attribute_id',
                ['value', 'entity_id']
            )
            ->where('cpev.value = ?', $url)
            ->where('cpev.store_id = ?', 0)
            ->where('ea.attribute_code = ?', 'url_key')
            ->where('ea.entity_type_id = ?', 4);

        $result = $connection->fetchRow($sql);

        if ($result) {
            return $result['entity_id'] ?? null;
        }

        return null;
    }
}
