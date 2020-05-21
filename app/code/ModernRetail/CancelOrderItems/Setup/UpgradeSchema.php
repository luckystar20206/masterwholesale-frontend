<?php

namespace ModernRetail\CancelOrderItems\Setup;

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


        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            $connection = $setup->getConnection();

            foreach(['sales_order_item'] as $entity) {
                $tableName = $setup->getTable($entity);
                if ($setup->getConnection()->isTableExists($tableName) == true) {

                    $connection->addColumn(
                        $tableName,
                        'qty_pre_canceled',
                        ['type' => Table::TYPE_INTEGER, 'nullable' => true, 'default' => '0',"comment"=>"Modern Retail Items Pre Canceled"]

                    );
                }


            }
        }



        $setup->endSetup();

    }
}