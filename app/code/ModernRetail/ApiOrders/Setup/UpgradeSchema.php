<?php

namespace ModernRetail\ApiOrders\Setup;

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


        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $connection = $setup->getConnection();

                foreach(['sales_order','sales_invoice','sales_creditmemo'] as $entity) {
                    $tableName = $setup->getTable($entity);
                    if ($setup->getConnection()->isTableExists($tableName) == true) {

                        $connection->addColumn(
                            $tableName,
                            'sent_to_mr_api',
                            ['type' => Table::TYPE_INTEGER, 'nullable' => false, 'default' => '0',"comment"=>"Flag to mark order was sent to MR API"]

                        );
                    }
 

             }
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $connection = $setup->getConnection();

            foreach(['sales_order_grid','sales_invoice_grid','sales_creditmemo_grid'] as $entity) {
                $tableName = $setup->getTable($entity);
                if ($setup->getConnection()->isTableExists($tableName) == true) {

                    $connection->addColumn(
                        $tableName,
                        'sent_to_mr_api',
                        ['type' => Table::TYPE_INTEGER, 'nullable' => false, 'default' => '0',"comment"=>"Flag to mark order was sent to MR API"]

                    );
                }

            }
        }

        if (version_compare($context->getVersion(), '0.0.9', '<')) {
            $connection = $setup->getConnection();

                $tableName = $setup->getTable('sales_order_item');
                if ($setup->getConnection()->isTableExists($tableName) == true) {


                    $connection->addColumn(
                        $tableName,
                        'location_id',
                        ['type' => Table::TYPE_TEXT, 'nullable' => true, "comment"=>"Order Item Type",'LENGTH'=>25]

                    );

                    $connection->addColumn(
                        $tableName,
                        'order_item_type',
                        ['type' => Table::TYPE_TEXT, 'nullable' => true, "comment"=>"Order Item Type",'default'=>'default','LENGTH'=>25]

                    );
                    $connection->addColumn(
                        $tableName,
                        'delivery_date',
                        ['type' => Table::TYPE_TEXT, 'nullable' => true, "comment"=>"Date When Order Item Should Be Sent To The Customer"]

                    );
                    $date = date('Y-m-d');

                    $table = $this->_resource->getTableName('core_config_data');
                    $this->connection->query("INSERT IGNORE INTO $table (scope,scope_id,path,value) 
                                                VALUES (default,0,'modernretail_base/order/start_send_orders_from','$date');");
                }

        }


        if (version_compare($context->getVersion(), '0.1.0', '<')) {

            /**
             * Create table 'greeting_message'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('mr_api_queue'))
                ->addColumn(
                    'queue_row_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Identification'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    25,
                    ['nullable' => false, 'default' => 'order'],
                    'Type'
                )->setComment("Type of entity")

                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    25,
                    ['nullable' => false],
                    'Type'
                )->setComment("Type of entity")

                ->addColumn(
                    'scheduled_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => NULL],
                    'Timedate when scheduled'
                )->setComment("Timedate when scheduled")

                ->addColumn(
                    'sent_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => NULL],
                    'Timedate when sent'
                )->setComment("Timedate when sent")

                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    25,
                    ['nullable' => true, 'default' => 'scheduled'],
                    'status'
                )->setComment("scheduled,pending,sent,failed")


                ->addColumn(
                    'request',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    NULL,
                    ['unsigned' => true, 'nullable' => true],
                    'request'
                )->setComment("request")


                ->addColumn(
                    'response',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    NULL,
                    ['unsigned' => true, 'nullable' => true],
                    'response'
                )->setComment("response");

            $setup->getConnection()->createTable($table);
        }



        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $connection = $setup->getConnection();
            $tableName = $setup->getTable('mr_api_queue');
            if ($setup->getConnection()->isTableExists($tableName) == true) {

                $connection->addColumn(
                    $tableName,
                    'tag',
                    ['type' => Table::TYPE_TEXT, 'nullable' => true, "comment" => "tag", 'LENGTH' => 25]

                );
            }
        }


        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $connection = $setup->getConnection();

            $tableName = $setup->getTable('quote_item');
            if ($setup->getConnection()->isTableExists($tableName) == true) {

                $connection->addColumn(
                    $tableName,
                    'location_id',
                    ['type' => Table::TYPE_TEXT, 'nullable' => true, "comment"=>"Quote Item Type",'LENGTH'=>25]

                );

                $connection->addColumn(
                    $tableName,
                    'order_item_type',
                    ['type' => Table::TYPE_TEXT, 'nullable' => true, "comment"=>"Quote Item Type",'default'=>'default','LENGTH'=>25]

                );
                $connection->addColumn(
                    $tableName,
                    'delivery_date',
                    ['type' => Table::TYPE_TEXT, 'nullable' => true, "comment"=>"Date When Order Item Should Be Sent To The Customer"]

                );

            }

        }





        $setup->endSetup();

    }
}