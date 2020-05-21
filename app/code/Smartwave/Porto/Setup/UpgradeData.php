<?php
/**
* Copyright © 2018 Porto. All rights reserved.
*/

namespace Smartwave\Porto\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface {

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '3.1.0') < 0) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'product_page_type', [
                'group' => 'Product Details',
                'type' => 'varchar',
                'sort_order' => 200,
                'backend' => '',
                'frontend' => '',
                'label' => 'Product Page Type',
                'input' => 'select',
                'source' => 'Smartwave\Porto\Model\Attribute\Productpagetype',
                'class' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'wysiwyg_enabled' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable'
            ]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'product_image_size', [
                'group' => 'Product Details',
                'type' => 'varchar',
                'sort_order' => 201,
                'backend' => '',
                'frontend' => '',
                'label' => 'Product Image Size',
                'input' => 'select',
                'source' => 'Smartwave\Porto\Model\Attribute\Productimagesize',
                'class' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'wysiwyg_enabled' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable'
            ]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'custom_block', [
                'group' => 'Product Details',
                'type' => 'text',
                'sort_order' => 202,
                'backend' => '',
                'frontend' => '',
                'label' => 'Custom Block',
                'input' => 'textarea',
                'class' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'wysiwyg_enabled' => true,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable'
            ]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'custom_block_2', [
                'group' => 'Product Details',
                'type' => 'text',
                'sort_order' => 203,
                'backend' => '',
                'frontend' => '',
                'label' => 'Custom Block 2',
                'input' => 'textarea',
                'class' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'wysiwyg_enabled' => true,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable'
            ]);
        }

        $setup->endSetup();
    }
}