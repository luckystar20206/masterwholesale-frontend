<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DeliveryTime\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $salesOrder = $installer->getTable('sales_order');
        if (!$connection->tableColumnExists($salesOrder, 'mp_delivery_information')) {
            if ($connection->tableColumnExists($salesOrder, 'osc_delivery_time')) {
                $connection->changeColumn(
                    $salesOrder,
                    'osc_delivery_time',
                    'mp_delivery_information',
                    ['type' => Table::TYPE_TEXT]
                );
            } else {
                $connection->addColumn(
                    $salesOrder,
                    'mp_delivery_information',
                    ['type' => Table::TYPE_TEXT, 'visible' => false, 'comment' => 'Mageplaza Delivery Time']
                );
            }
        }

        $installer->endSetup();
    }
}
