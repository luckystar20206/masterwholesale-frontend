<?php
/**
* Copyright © 2018 Porto. All rights reserved.
*/

namespace Smartwave\Megamenu\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
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
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.1.0') < 0) {
            // set new resource model paths
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
            
            $menu_attributes = [
                'sw_ocat_hide_this_item' => [
                    'type' => 'int',
                    'label' => 'Hide This Category',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'required' => false,
                    'sort_order' => 10,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Onepage Category'
                ],
                'sw_ocat_category_icon_image' => [
                    'type' => 'varchar',
                    'label' => 'Icon Image',
                    'input' => 'image',
                    'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                    'required' => false,
                    'sort_order' => 20,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Onepage Category'
                ],
                'sw_ocat_category_font_icon' => [
                    'type' => 'varchar',
                    'label' => 'Font Icon Class',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 30,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Onepage Category',
                    'note' => 'If this category has no "Icon Image", font icon will be shown. example to input: icon-dollar'
                ],
                'sw_ocat_category_hoverbgcolor' => [
                    'type' => 'varchar',
                    'label' => 'Category Hover Background Color',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 40,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Onepage Category',
                    'note' => 'eg: #00d59d'
                ],
                'sw_ocat_additional_content' => [
                    'type' => 'text',
                    'label' => 'Additional Content',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 50,
                    'wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Onepage Category'
                ]
            ];
            
            foreach($menu_attributes as $item => $data) {
                $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
            }
            
            $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Onepage Category');
            
            foreach($menu_attributes as $item => $data) {
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $idg,
                    $item,
                    $data['sort_order']
                );
            }
        }
        
        $setup->endSetup();
    }
}
