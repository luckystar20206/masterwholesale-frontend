<?php
namespace Smartwave\Dailydeals\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('sw_dailydeals_dailydeal')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('sw_dailydeals_dailydeal')
            )
            ->addColumn(
                'dailydeal_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Dailydeal ID'
            )
            ->addColumn(
                'sw_product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Dailydeal Product Sku'
            )
            ->addColumn(
                'sw_product_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Dailydeal Product Price'
            )
            ->addColumn(
                'sw_deal_enable',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Dailydeal Enable Deal'
            )
            ->addColumn(
                'sw_discount_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Dailydeal Discount Type'
            )
            ->addColumn(
                'sw_discount_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Dailydeal Discount Amount'
            )
            ->addColumn(
                'sw_date_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Dailydeal Date From'
            )
            ->addColumn(
                'sw_date_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Dailydeal Date To'
            )

            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Dailydeal Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Dailydeal Updated At'
            )
            ->setComment('Dailydeal Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('sw_dailydeals_dailydeal'),
                $setup->getIdxName(
                    $installer->getTable('sw_dailydeals_dailydeal'),
                    ['sw_product_sku'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['sw_product_sku'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $installer->endSetup();
    }
}
