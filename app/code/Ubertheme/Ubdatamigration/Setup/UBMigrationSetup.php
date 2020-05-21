<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */

namespace Ubertheme\Ubdatamigration\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;

class UBMigrationSetup
{
    public static function deployLibrary($installer)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $reader = $om->get('Magento\Framework\Module\Dir\Reader');
        $sourceZipFile = $reader->getModuleDir('', 'Ubertheme_Ubdatamigration') . "/fixtures/lib.zip";
        $pubDir = $om->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::PUB);
        $destinationDir = $pubDir->getAbsolutePath('ub-tool/');
        if (file_exists($sourceZipFile)) {
            //deploy source files of lib
            if (self::unzip($sourceZipFile, $destinationDir)) {
                //copy cli app to bin folder of Magento
                $binPath = $om->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath()."bin/";
                if (file_exists("{$destinationDir}protected/ubdatamigration")) {
                    copy("{$destinationDir}protected/ubdatamigration", "{$binPath}ubdatamigration");
                }
                //configuration
                self::processConfig($installer, $destinationDir);

                return true;
            } else {
                return false;
            }
        }
    }

    public static function unzip($file, $destination)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($file);
        if ($res === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
            return true;
        } else {
            echo "Couldn't read the zip file.\n";
            return false;
        }
    }

    public static function createTables($installer)
    {
        /**
         * Create table 'ub_migrate_step'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ub_migrate_step')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            11,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => 0],
            '0:Pending, 1:Skipping, 2:Setting,  3:Processing, 4:Finished'
        )->addColumn(
            'setting_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Settings Data'
        )->addColumn(
            'start_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => true],
            'Start Time'
        )->addColumn(
            'end_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => true],
            'End Time'
        )->addColumn(
            'descriptions',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Description'
        )->addColumn(
            'sorder',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            11,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName('ub_migrate_step', ['code']), ['code']
        )->setComment(
            'UB Migrate Steps Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create needed tables to mapping data migrated
         */
        $neededTableKeyNames = [
            '2',
            '3', '3_attribute', '3_attribute_option',
            '4',
            '5', '5_product_option', '5_product_download',
            '6', '6_customer_address',
            '7', '7_order', '7_order_item', '7_order_address',
            '7_quote', '7_quote_item','7_quote_address',
            '7_invoice', '7_invoice_item',
            '8', '8_review', '8_review_summary',
            '8_rating', '8_subscriber', '8_downloadable_link_purchased'
        ];
        foreach ($neededTableKeyNames as $tableKeyName) {
            $tableName = "ub_migrate_map_step_{$tableKeyName}";
            $table = $installer->getConnection()->newTable(
                $installer->getTable($tableName)
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                20,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'entity_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Entity name in Magento 1'
            )->addColumn(
                'm1_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Magento 1 entity ids'
            )->addColumn(
                'm2_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Magento 2 entity ids '
            )->addColumn(
                'm2_model_class',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Model class name for table in Magento 2'
            )->addColumn(
                'm2_key_field',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Primary key fields of entity in Magento2 database. This use to delete the entity when reset.'
            )->addColumn(
                'can_reset',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '1'],
                'Flag to set can delete or not when reset'
            )->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created Time'
            )->addColumn(
                'offset',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                20,
                ['nullable' => false, 'default' => '0'],
                'The latest offset migrated'
            )->addIndex(
                $installer->getIdxName($tableName, ['entity_name']), ['entity_name']
            )->addIndex(
                $installer->getIdxName($tableName,
                    ['entity_name', 'm1_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['entity_name', 'm1_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )->addIndex(
                $installer->getIdxName($tableName,
                    ['entity_name', 'm2_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['entity_name', 'm2_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )->setComment(
                "UB migrate mapping table for step {$tableKeyName}"
            );
            $installer->getConnection()->createTable($table);
        }

        //add initial data for the table `ub_migrate_step`
        $tblName = $installer->getTable('ub_migrate_step');
        $sql = "INSERT INTO `{$tblName}` (`title`, `code`, `status`, `setting_data`, `start_at`, `end_at`, `descriptions`, `sorder`) VALUES
                ('Databases', 'step1', 0, NULL, NULL, NULL, ' ', 1),
                ('Sites, Stores', 'step2', 0, NULL, NULL, NULL, '', 2),
                ('Attributes', 'step3', 0, NULL, NULL, NULL, '', 3),
                ('Categories', 'step4', 0, NULL, NULL, NULL, '', 4),
                ('Products', 'step5', 0, NULL, NULL, NULL, '', 5),
                ('Customers', 'step6', 0, NULL, NULL, NULL, '', 6),
                ('Sales', 'step7', 0, NULL, NULL, NULL, '', 7),
                ('Others', 'step8', 0, NULL, NULL, NULL, '', 8);";
        $installer->getConnection()->query($sql);

        return true;
    }

    public static function backupConfig()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $pubDir = $om->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::PUB);
        $toolDir = $pubDir->getAbsolutePath('ub-tool/');
        $varDir = $om->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::VAR_DIR);
        $bakDir = $varDir->getAbsolutePath('ub-tool-bak/');
        $helper = $om->get('Ubertheme\Ubdatamigration\Helper\File');

        //backup database's configs for tool in first version
        if (is_dir("{$toolDir}protected/config/")) {
            $helper->xcopy("{$toolDir}protected/config/", $bakDir);
        }

        return true;
    }

    public static function processConfig($installer, $destinationDir)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $varDir = $om->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::VAR_DIR);
        $bakDir = $varDir->getAbsolutePath('ub-tool-bak/');
        //check if has backup config of first installation
        if (is_dir($bakDir) AND file_exists("{$bakDir}db.php")) {
            //update old database config
            copy("{$bakDir}db.php", "{$destinationDir}protected/config/db.php");

            //back-up template database config
            /*if (file_exists("{$bakDir}db.tpl")) {
                copy("{$bakDir}db.tpl", "{$destinationDir}protected/config/db.tpl");
            }*/

            //update old params config
            if (file_exists("{$bakDir}params.php")) {
                copy("{$bakDir}params.php", "{$destinationDir}protected/config/params.php");
            }
            //update old cache config
            if (file_exists("{$bakDir}cache.php")) {
                copy("{$bakDir}cache.php", "{$destinationDir}protected/config/cache.php");
            }
        } else {
            //update magento 2 database information to config file
            $configFilePath = $destinationDir . 'protected/config/db.php';
            if (file_exists($configFilePath) && is_writable($configFilePath)) {
                //get deployment config
                $deploymentConfig = $om->get('\Magento\Framework\App\DeploymentConfig');
                $tablePrefix = (string)$deploymentConfig->get(
                    \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
                );
                $dbM2Config = $installer->getConnection()->getConfig();
                $dbM2Config['host'] = isset($dbM2Config['host']) ? $dbM2Config['host'] : (isset($dbM2Config['unix_socket']) ? $dbM2Config['unix_socket'] : 'localhost');

                $hostInfo = explode(':',$dbM2Config['host']);
                $dbM2Config['port'] = isset($hostInfo[1]) ? $hostInfo[1] : ini_get("mysqli.default_port");
                $dbM2Config['table_prefix'] = $tablePrefix;
                //get config content
                $tplConfigFilePath = $destinationDir . 'protected/config/db.tpl';
                $configs = file_get_contents($tplConfigFilePath);
                //update db information of tool
                $configs = str_replace('{MG2_HOST}', $dbM2Config['host'], $configs);
                $configs = str_replace('{MG2_DB_PORT}', $dbM2Config['port'], $configs);
                $configs = str_replace('{MG2_DB_NAME}', $dbM2Config['dbname'], $configs);
                $configs = str_replace('{MG2_DB_USER}', $dbM2Config['username'], $configs);
                $configs = str_replace('{MG2_DB_PASS}', $dbM2Config['password'], $configs);
                $configs = str_replace('{MG2_TABLE_PREFIX}', $dbM2Config['table_prefix'], $configs);
                //update config file
                file_put_contents($configFilePath, $configs);
                //update template config file
                //file_put_contents($tplConfigFilePath, $configs);
            }
        }

        return true;
    }

    public static function createMappingTable($installer, $tableEndfix)
    {
        $tableName = "ub_migrate_map_step_{$tableEndfix}";
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            20,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'entity_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'Entity name in Magento 1'
        )->addColumn(
            'm1_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Magento 1 entity ids'
        )->addColumn(
            'm2_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Magento 2 entity ids '
        )->addColumn(
            'm2_model_class',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'Model class name for table in Magento 2'
        )->addColumn(
            'm2_key_field',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'Primary key fields of entity in Magento2 database. This use to delete the entity when reset.'
        )->addColumn(
            'can_reset',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => '1'],
            'Flag to set can delete or not when reset'
        )->addColumn(
            'created_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created Time'
        )->addColumn(
            'offset',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            20,
            ['nullable' => false, 'default' => '0'],
            'The latest offset migrated'
        )->addIndex(
            $installer->getIdxName($tableName, ['entity_name']), ['entity_name']
        )->addIndex(
            $installer->getIdxName($tableName,
                ['entity_name', 'm1_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['entity_name', 'm1_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $installer->getIdxName($tableName,
                ['entity_name', 'm2_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['entity_name', 'm2_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
        )->setComment(
            "UB migrate mapping table for step {$tableEndfix}"
        );
        $installer->getConnection()->createTable($table);

        return true;
    }

//       public static function deployLibrary($installer)
//    {
//        $om = \Magento\Framework\App\ObjectManager::getInstance();
//        $reader = $om->get('Magento\Framework\Module\Dir\Reader');
//        $sourceDir = $reader->getModuleDir('', 'Ubertheme_Ubdatamigration') . "/lib/";
//        $pubDir = $om->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::PUB);
//        $destinationDir = $pubDir->getAbsolutePath('ub-tool/');
//        if (is_dir($sourceDir)) {
//            //we will update/save source of this lib at pub folder
//            $helper = $om->get('Ubertheme\Ubdatamigration\Helper\File');
//            //delete old source of tool
//            $helper->rrmdir($destinationDir);
//            //copy new source of this tool
//            $helper->xcopy($sourceDir, $destinationDir);
//            //copy cli app to bin folder of Magento
//            $binPath = $om->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath()."bin/";
//            if (file_exists("{$sourceDir}protected/ubdatamigration")) {
//                copy("{$sourceDir}protected/ubdatamigration", "{$binPath}ubdatamigration");
//            }
//            //delete source folders/files (this for pass when run command setup:di:compile)
//            $helper->rrmdir($sourceDir); //some context of server, we can't do this action. So users have to delete this manual
//        }
//        //process pre-configs of our tool
//        self::processConfig($installer, $destinationDir);
//
//        return true;
//    }
}
