<?php
declare(strict_types=1);

namespace MageWorx\DeliveryDate\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Psr\Log\LoggerInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddEstimatedDeliveryTimeProductAttributes implements DataPatchInterface,
    PatchRevertableInterface
{
    const DELIVERY_OPTIONS_GROUP_ID = 'Delivery Options';

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $setup
     * @param MetadataPool $metadataPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        EavSetupFactory          $eavSetupFactory,
        ModuleDataSetupInterface $setup,
        MetadataPool             $metadataPool,
        LoggerInterface          $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup           = $setup;
        $this->metadataPool    = $metadataPool;
        $this->logger          = $logger;
    }

    /**
     * Patch dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [
            \MageWorx\DeliveryDate\Setup\Patch\Data\FixAttributeCode::class
        ];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - then under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return $this
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        // Creating attribute group named 'Delivery Options'
        $eavSetup->addAttributeGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId,
            static::DELIVERY_OPTIONS_GROUP_ID,
            20
        );

        $attributeUseEDT = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mw_delivery_time_enabled'
        );
        if (empty($attributeUseEDT)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mw_delivery_time_enabled',
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'int',
                    'label'                    => 'Allow Delivery Date',
                    'note'                     => 'This setting allows you to disable the delivery date functionality for this product.
            The delivery date feature on the checkout will be hidden if this product is added to the cart.',
                    'input'                    => 'select',
                    'required'                 => false,
                    'sort_order'               => 10,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    'default'                  => 1,
                    'source'                   => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false
                    // should be false to prevent deleting from admin-side interface
                ]
            );
        } else {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                static::DELIVERY_OPTIONS_GROUP_ID,
                'mw_delivery_time_enabled',
                10
            );
        }

        $this->setProductAttributeDefaultValue("mw_delivery_time_enabled", 1);

        $attributeDisplayEDT = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mw_delivery_time_visible'
        );
        if (empty($attributeDisplayEDT)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mw_delivery_time_visible',
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'int',
                    'label'                    => 'Display Delivery Date',
                    'frontend_label'           => 'Display Estimated Delivery Date',
                    'note'                     => 'This setting displays the estimated delivery period on the product page.',
                    'input'                    => 'select',
                    'required'                 => false,
                    'sort_order'               => 15,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    'default'                  => 1,
                    'source'                   => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false
                    // should be false to prevent deleting from admin-side interface
                ]
            );
        } else {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                static::DELIVERY_OPTIONS_GROUP_ID,
                'mw_delivery_time_visible',
                15
            );
        }

        $this->setProductAttributeDefaultValue("mw_delivery_time_visible", 0);

        $attributeFrom = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mw_delivery_time_from'
        );
        if (empty($attributeFrom)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mw_delivery_time_from',
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'text',
                    'label'                    => 'Estimated Delivery Period: From',
                    'input'                    => 'text',
                    'required'                 => false,
                    'sort_order'               => 30,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front'         => false,
                    'default'                  => '0',
                    'system'                   => 0,
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false
                    // should be false to prevent deleting from admin-side interface
                ]
            );
        } else {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                static::DELIVERY_OPTIONS_GROUP_ID,
                'mw_delivery_time_from',
                30
            );
        }

        $attributeTo = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mw_delivery_time_to'
        );
        if (empty($attributeTo)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mw_delivery_time_to',
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'text',
                    'label'                    => 'Estimated Delivery Period: To',
                    'input'                    => 'text',
                    'required'                 => false,
                    'sort_order'               => 42,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false
                    // should be false to prevent deleting from admin-side interface
                ]
            );
        } else {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                static::DELIVERY_OPTIONS_GROUP_ID,
                'mw_delivery_time_to',
                40
            );
        }

        $nonWorkingDaysAttribute = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mw_non_working_days'
        );
        if (empty($nonWorkingDaysAttribute)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mw_non_working_days',
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'text',
                    'label'                    => 'Non-Working Days',
                    'input'                    => 'multiselect',
                    'required'                 => false,
                    'sort_order'               => 50,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => false,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    'note'                     => 'You can select the non-working days for current product,
                    i.e. the days, when you do not deliver the certain product.
                    Leave empty to use global settings.',
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false,
                    // should be false to prevent deleting from admin-side interface
                    'source'                   => \MageWorx\DeliveryDate\Model\Product\Attribute\Source\WeekDays::class,
                    'backend'                  => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                    // Extends Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend
                ]
            );
        }

        $cutOffTimeAttribute = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mw_cut_off_time'
        );
        if (empty($cutOffTimeAttribute)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mw_cut_off_time',
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'text',
                    'label'                    => 'Cutoff Time',
                    'input'                    => 'text',
                    'required'                 => false,
                    'sort_order'               => 60,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => false,
                    'is_visible_in_grid'       => false,
                    'is_filterable_in_grid'    => false,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => false,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    'note'                     => 'This setting defines the time, when the next available delivery date
                    is disabled for the selection, if this product is added to the cart. The product cutoff time has
                    higher priority than the global cutoff time.',
                    'user_defined'             => false
                ]
            );
        }

        $attributeFrom = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_FROM
        );
        if (empty($attributeFrom)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_FROM,
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'datetime',
                    'label'                    => 'Available From',
                    'input'                    => 'date',
                    'required'                 => false,
                    'sort_order'               => 42,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false,
                    // should be false to prevent deleting from admin-side interface
                    'default_value'            => ''
                ]
            );
        } else {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                static::DELIVERY_OPTIONS_GROUP_ID,
                \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_FROM,
                42
            );
        }

        $attributeTo = $eavSetup->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_TO
        );
        if (empty($attributeTo)) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_TO,
                [
                    'group'                    => static::DELIVERY_OPTIONS_GROUP_ID,
                    'type'                     => 'datetime',
                    'label'                    => 'Available To',
                    'input'                    => 'date',
                    'required'                 => false,
                    'sort_order'               => 44,
                    'global'                   => ScopedAttributeInterface::SCOPE_STORE,
                    'is_used_in_grid'          => true,
                    'is_visible_in_grid'       => true,
                    'is_filterable_in_grid'    => true,
                    'visible'                  => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front'         => false,
                    'system'                   => 0,
                    // should be 0 to access this attribute everywhere
                    'user_defined'             => false,
                    // should be false to prevent deleting from admin-side interface
                    'default_value'            => ''
                ]
            );
        } else {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                static::DELIVERY_OPTIONS_GROUP_ID,
                \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_TO,
                44
            );
        }

        return $this;
    }

    /**
     * Rollback all changes, done by this patch
     *
     * @return void
     */
    public function revert()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $attributes = [
            'mw_delivery_time_enabled',
            'mw_delivery_time_from',
            'mw_delivery_time_to',
            \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_TO,
            \MageWorx\DeliveryDate\Api\QuoteMinMaxDateInterface::MW_DELIVERY_DATE_AVAILABLE_FROM,
            'mw_cut_off_time',
            'mw_delivery_time_visible',
            'mw_non_working_days'

        ];

        foreach ($attributes as $productAttributeCode) {
            $attribute = $eavSetup->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $productAttributeCode
            );
            if (!empty($attribute)) {
                $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $productAttributeCode);
            }
        }

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $deliveryGroup  = $eavSetup->getAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            static::DELIVERY_OPTIONS_GROUP_ID
        );
        if (!empty($deliveryGroup)) {
            $eavSetup->removeAttributeGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                static::DELIVERY_OPTIONS_GROUP_ID
            );
        }
    }

    /**
     * Updates existing products, set default value of the attribute
     *
     * @param $attributeCode
     * @param $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setProductAttributeDefaultValue($attributeCode, $value)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        $setup    = $this->setup;

        $attributeTableFields = $setup->getConnection()
                                      ->describeTable(
                                          $setup->getTable('catalog_product_entity_int')
                                      );

        $productTypeId          = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $selectProductAttribute = $setup->getConnection()->select();

        $selectProductAttribute
            ->from(['ea' => $setup->getTable('eav_attribute')], ['attribute_id'])
            ->where("`entity_type_id` = '" . $productTypeId . "'")
            ->where("attribute_code = ?", $attributeCode);

        $productAttributeId = $setup->getConnection()->fetchOne($selectProductAttribute);
        if (is_numeric($productAttributeId)) {
            $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

            $productAttributeValueInsert =
                $setup->getConnection()
                      ->select()
                      ->from(
                          ['e1' => $setup->getTable('catalog_product_entity')],
                          array_merge(
                              $attributeTableFields,
                              [
                                  'value_id'     => new \Zend_Db_Expr('NULL'),
                                  'attribute_id' => new \Zend_Db_Expr($productAttributeId),
                                  'store_id'     => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                                  $linkField     => 'e1.' . $linkField,
                                  'value'        => new \Zend_Db_Expr($value),
                              ]
                          )
                      )
                      ->where(
                          'e1.' . $linkField . ' NOT IN(' . new \Zend_Db_Expr(
                              "SELECT `" . $linkField . "` FROM " . $setup->getTable(
                                  'catalog_product_entity_int'
                              ) .
                              " WHERE `store_id` = 0 AND `attribute_id` = " . $productAttributeId . ")"
                          )
                      )
                      ->order('e1.' . $linkField . ' ' . Select::SQL_ASC)
                      ->insertFromSelect($setup->getTable('catalog_product_entity_int'));

            $setup->run($productAttributeValueInsert);
        }
    }
}
