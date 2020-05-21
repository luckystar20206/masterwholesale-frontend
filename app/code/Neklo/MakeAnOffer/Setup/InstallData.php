<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) // @codingStandardsIgnoreLine
    {
        $productTypes = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ];
        $productTypes = join(',', $productTypes);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'allow_make_an_offer_product',
            [
                'group'                          => 'Product Details',
                'type'                           => 'int',
                'backend'                        => '',
                'frontend'                       => '',
                'label'                          => 'Allow Make An Offer',
                'input'                          => 'select',
                'class'                          => '',
                'source'                         => \Neklo\MakeAnOffer\Model\Source\Attribute\Product::class,
                'global'
                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible'                        => true,
                'required'                       => true,
                'user_defined'                   => true,
                'default'                        => '1',
                'searchable'                     => false,
                'filterable'                     => false,
                'comparable'                     => false,
                'visible_on_front'               => false,
                'used_in_product_listing'        => true,
                'unique'                         => false,
                'apply_to'                       => $productTypes,
            ]
        );

        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'allow_make_an_offer_category', [
            'type'     => 'int',
            'label'    => 'Allow Make An Offer',
            'input'    => 'select',
            'source'   => \Neklo\MakeAnOffer\Model\Source\Attribute\Category::class,
            'visible'  => true,
            'required' => false,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'group'    => 'Make An Offer',
        ]);

        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'assign_make_an_offer_category', [
            'type'     => 'int',
            'label'    => 'Update All Products Assigned to Category',
            'input'    => 'select',
            'visible'  => true,
            'default'  => false,
            'required' => false,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'group'    => 'Make An Offer',
        ]);
    }
}
