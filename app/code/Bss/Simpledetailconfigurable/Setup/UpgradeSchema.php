<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addProductEnabledTable($setup);
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addAjaxDetailColumn($setup);
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->addCustomUrlTable($setup);
        }
        $setup->endSetup();
    }
    public function addProductEnabledTable($setup)
    {
        if (!$setup->tableExists('sdcp_product_enabled')) {
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('sdcp_product_enabled')
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'Enabled'
                )->addIndex(
                    $setup->getIdxName('sdcp_product_enabled', ['product_id']),
                    ['product_id']
                )
                ->setComment(
                    'Preselect key for configurable product'
                );
                $setup->getConnection()->createTable($table);
        }
    }

    public function addAjaxDetailColumn($setup)
    {
        $bssSdcpTable = $setup->getTable('sdcp_product_enabled');
        $connection = $setup->getConnection();
        if ($connection->isTableExists($bssSdcpTable)) {
            $connection->addColumn(
                $bssSdcpTable,
                'is_ajax_load',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 6,
                    'nullable' => false,
                    'comment' => 'enable ajax load'
                ]
            );
        }
    }

    public function addCustomUrlTable($setup)
    {

        if (!$setup->tableExists('sdcp_custom_url')) {
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('sdcp_custom_url')
                )
                ->addColumn(
                    'url_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'auto_increment' => true, 'nullable' => false],
                    'Key ID'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'custom_url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Custom Url'
                )
                ->addColumn(
                    'parent_url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Parent Url'
                )->addIndex(
                    $setup->getIdxName('sdcp_custom_url', ['product_id']),
                    ['product_id']
                )
                ->setComment(
                    'Preselect key for configurable product'
                );
                $setup->getConnection()->createTable($table);
        }
    }
}
