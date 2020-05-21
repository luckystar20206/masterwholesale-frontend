<?php
namespace Mwi\StoreTiming\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '1.0.5', '<')) {
			$installer->getConnection()->changeColumn(
				$installer->getTable( 'storetiming_timing' ),
				'date',
				'date',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					'nullable' => true,
					'comment' => 'date'
				]
			);
			$installer->getConnection()->changeColumn(
				$installer->getTable( 'storetiming_timing' ),
				'created_at',
				'created_at',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					'nullable' => false,
					'comment' => 'created_at',
					'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
				]
			);
		}

		$installer->endSetup();
	}
}
