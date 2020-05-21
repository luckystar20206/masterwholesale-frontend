<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Bannerslider
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace ModernRetail\TotalReport\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
/**
 * Install schema
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        //ci($installer);
        $installer->run("CREATE TABLE IF NOT EXISTS {$installer->getTable('mr_totalreport')}
       	(
			id  int(11) not null auto_increment primary key,
       		order_id int(10) unsigned not null,
       		order_increment varchar(25) not null,
       		subtotal decimal(10,2) not null default 0.00,
       		type enum('debit','credit') not null default 'credit',
       		operation_date datetime not null
       	)engine=innodb;");

        $installer->getConnection();
        /*
         * End create table magestore_bannerslider_report
         */
        $installer->endSetup();
    }
}