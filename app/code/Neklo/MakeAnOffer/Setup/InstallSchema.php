<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install( // @codingStandardsIgnoreLine
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $this->createRequestTable($setup);
        $this->createStatisticTable($setup);

        $setup->endSetup();
    }

    /**
     * @param $setup
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function createRequestTable($setup)
    {
        if (!$setup->tableExists($setup->getTable('neklo_make_an_offer_request'))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('neklo_make_an_offer_request'))

                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Post ID'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Email'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    255,
                    ['nullable' => true],
                    'Customer ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Product ID'
                )
                ->addColumn(
                    'product_options',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Product Options'
                )
                ->addColumn(
                    'product_sku',
                    Table::TYPE_TEXT,
                    1000,
                    ['nullable => true'],
                    'Product Sku'
                )
                ->addColumn(
                    'price',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['default' => '0.0000'],
                    'Original Price'
                )
                ->addColumn(
                    'request_price',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['default' => '0.0000'],
                    'Requested Price'
                )
                ->addColumn(
                    'requested_sale_amount',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Requested Sale Amount'
                )
                ->addColumn(
                    'applied_coupon_amount',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Applied Coupon Amount'
                )
                ->addColumn(
                    'product_qty',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Product quantity'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'link',
                    Table::TYPE_TEXT,
                    1000,
                    ['nullable => true'],
                    'Link'
                )
                ->addColumn(
                    'coupon',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Coupon code'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    1,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Request status'
                )
                ->addColumn(
                    'sold_price',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Sold Price'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => true,
                        'default'  => null,
                        'unsigned' => true,
                    ],
                    'Order ID'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        $setup->getTable('neklo_make_an_offer_request'),
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $setup->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex('ORDER_ID', 'order_id')
                ->addIndex('CUSTOMER_ID', 'customer_id')
                ->setComment('Offers Request Table');
            $setup->getConnection()->createTable($table);
        }
    }

    private function createStatisticTable($setup)
    {
        if (!$setup->tableExists($setup->getTable('neklo_make_an_offer_statistic'))) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('neklo_make_an_offer_statistic')
            )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Record ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Product ID'
                )
                ->addColumn(
                    'product_sku',
                    Table::TYPE_TEXT,
                    1000,
                    ['nullable => true'],
                    'Product Sku'
                )
                ->addColumn(
                    'declined_qty',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => 0
                    ],
                    'Declined Qty'
                )
                ->addColumn(
                    'accepted_qty',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => 0
                    ],
                    'Accepted Qty'
                )
                ->addColumn(
                    'ordered_qty',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => 0
                    ],
                    'Order Qty'
                )
                ->addColumn(
                    'total_discount',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    [
                        'nullable' => true,
                        'default' => 0
                    ],
                    'Total Discount Amount'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        $setup->getTable('neklo_make_an_offer_statistic'),
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $setup->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );
            $setup->getConnection()->createTable($table);
        }
    }
}
