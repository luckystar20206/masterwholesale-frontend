<?php

namespace ModernRetail\Import\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function __construct(

        \Magento\Framework\App\ResourceConnection $resource
    )
    {

        $this->_resource = $resource;
        $this->connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.9.9', '<=')) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('mr_import'))
                ->addColumn(
                    'row_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Identification'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => false, 'default' => 'np'],
                    'Type'
                )->setComment("Type of File")

                ->addColumn(
                    'date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => new \Zend_Db_Expr("NOW()")],
                    'Type'
                )->setComment("DATE")

                ->addColumn(
                    'file',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    25,
                    ['nullable' => false],
                    'Type'
                )->setComment("FileName")

                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    25,
                    ['nullable' => true, 'default' => 'scheduled'],
                    'status'
                )->setComment("received,pending,success,failed");

            $setup->getConnection()->createTable($table);
        }



        if (version_compare($context->getVersion(), '3.0.0', '<=')) {
            if (!$setup->tableExists('mr_import_log')) {
                $table = $setup->getConnection()->newTable(
                    $setup->getTable('mr_import_log')
                )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary' => true,
                            'unsigned' => true,
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'status',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'Import Status'
                    )
                    ->addColumn(
                        'file_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        100,
                        ['nullable => false'],
                        'Import File Id'
                    )
                    ->addColumn(
                        'type',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable => false'],
                        'Import Type'
                    )
                    ->addColumn(
                        'file_name',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        [],
                        'Import File Name'
                    )
                    ->addColumn(
                        'date',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'Import Date')
                    ->addColumn(
                        'message',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        '1M',
                        [],
                        'Import Message'
                    )
                    ->addColumn(
                        'path_to_file',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        [],
                        'Path To Import File'
                    )
                    ->setComment('Modern Retail Import Table');
                $setup->getConnection()->createTable($table);
            }
        }






        $setup->endSetup();

    }
}