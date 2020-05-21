<?php
/**
* Copyright © 2018 Porto. All rights reserved.
*/

namespace Smartwave\Megamenu\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
 
    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        
        $installer->startSetup();
        
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        
        $menu_attributes = [
            'sw_menu_hide_item' => [
                'type' => 'int',
                'label' => 'Hide This Menu Item',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'sort_order' => 10,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_type' => [
                'type' => 'varchar',
                'label' => 'Menu Type',
                'input' => 'select',
                'source' => 'Smartwave\Megamenu\Model\Attribute\Menutype',
                'required' => false,
                'sort_order' => 20,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_static_width' => [
                'type' => 'varchar',
                'label' => 'Static Width',
                'input' => 'text',
                'required' => false,
                'sort_order' => 30,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_cat_columns' => [
                'type' => 'varchar',
                'label' => 'Sub Category Columns',
                'input' => 'select',
                'source' => 'Smartwave\Megamenu\Model\Attribute\Subcatcolumns',
                'required' => false,
                'sort_order' => 40,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_float_type' => [
                'type' => 'varchar',
                'label' => 'Float',
                'input' => 'select',
                'source' => 'Smartwave\Megamenu\Model\Attribute\Floattype',
                'required' => false,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_cat_label' => [
                'type' => 'varchar',
                'label' => 'Category Label',
                'input' => 'select',
                'source' => 'Smartwave\Megamenu\Model\Attribute\Categorylabel',
                'required' => false,
                'sort_order' => 60,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_icon_img' => [
                'type' => 'varchar',
                'label' => 'Icon Image',
                'input' => 'image',
                'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'required' => false,
                'sort_order' => 70,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_font_icon' => [
                'type' => 'varchar',
                'label' => 'Font Icon Class',
                'input' => 'text',
                'required' => false,
                'sort_order' => 80,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_block_top_content' => [
                'type' => 'text',
                'label' => 'Top Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 90,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_block_left_width' => [
                'type' => 'varchar',
                'label' => 'Left Block Width',
                'input' => 'select',
                'source' => 'Smartwave\Megamenu\Model\Attribute\Width',
                'required' => false,
                'sort_order' => 100,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_block_left_content' => [
                'type' => 'text',
                'label' => 'Left Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 110,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_block_right_width' => [
                'type' => 'varchar',
                'label' => 'Right Block Width',
                'input' => 'select',
                'source' => 'Smartwave\Megamenu\Model\Attribute\Width',
                'required' => false,
                'sort_order' => 120,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_block_right_content' => [
                'type' => 'text',
                'label' => 'Right Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 130,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ],
            'sw_menu_block_bottom_content' => [
                'type' => 'text',
                'label' => 'Bottom Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 140,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'SW Menu'
            ]
        ];
        
        foreach($menu_attributes as $item => $data) {
            $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
        }
        
        $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'SW Menu');
        
        foreach($menu_attributes as $item => $data) {
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idg,
                $item,
                $data['sort_order']
            );
        }

        $installer->endSetup();
    }
}