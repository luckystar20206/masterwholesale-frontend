<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Cron\Task;

/**
 * Class ArchiveOrdersEntities
 * @package Ess\M2ePro\Model\Cron\Task
 */
class ArchiveOrdersEntities extends AbstractModel
{
    const NICK = 'archive_orders_entities';
    const MAX_MEMORY_LIMIT = 512;

    const MAX_ENTITIES_COUNT_FOR_ONE_TIME = 1000;

    const COUNT_EXCEEDS_TRIGGER = 100000;
    const DAYS_EXCEEDS_TRIGGER  = 180;

    //########################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //########################################

    protected function performActions()
    {
        $affectedOrders = $this->getAffectedOrdersGroupedByComponent();

        foreach ($this->getHelper('Component')->getEnabledComponents() as $component) {
            if (empty($affectedOrders[$component])) {
                continue;
            }

            $this->processComponentEntities($component, $affectedOrders[$component]);
        }

        return true;
    }

    //########################################

    private function getAffectedOrdersGroupedByComponent()
    {
        $connRead = $this->resource->getConnection();
        $firstAffectedId = $connRead->select()
            ->from(
                $this->activeRecordFactory->getObject('Order')->getResource()->getMainTable(),
                ['id']
            )
            ->order('id DESC')
            ->limit(1, self::COUNT_EXCEEDS_TRIGGER)
            ->query()->fetchColumn();

        if ($firstAffectedId === false) {
            return [];
        }

        $archiveFromDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $archiveFromDate->modify('- ' .self::DAYS_EXCEEDS_TRIGGER. ' days');

        $queryStmt = $connRead->select()
            ->from(
                $this->activeRecordFactory->getObject('Order')->getResource()->getMainTable(),
                ['id', 'component_mode']
            )
            ->where('id <= ?', (int)$firstAffectedId)
            ->where('create_date <= ?', $archiveFromDate->format('Y-m-d H:i:s'))
            ->limit(self::MAX_ENTITIES_COUNT_FOR_ONE_TIME)
            ->query();

        $orders = [];
        while ($row = $queryStmt->fetch()) {
            $orders[$row['component_mode']][] = (int)$row['id'];
        }

        return $orders;
    }

    private function processComponentEntities($componentName, array $componentOrdersIds)
    {
        $coreResource = $this->resource;

        $mainOrderTable = $this->activeRecordFactory->getObject('Order')->getResource()->getMainTable();
        $componentOrderTable = $this->activeRecordFactory
                                    ->getObject(ucfirst($componentName).'\Order')
                                    ->getResource()->getMainTable();

        $queryStmt = $coreResource->getConnection()->select()
            ->from(['main_table' => $mainOrderTable])
            ->joinInner(
                ['second_table' => $componentOrderTable],
                'second_table.order_id = main_table.id'
            )
            ->where('main_table.id IN (?)', $componentOrdersIds)
            ->query();

        $insertsData = [];

        while ($orderRow = $queryStmt->fetch()) {
            $insertsData[$orderRow['id']] = [
                'name' => 'Order',
                'origin_id' => $orderRow['id'],
                'data' => [
                    'order_data' => $orderRow
                ],
                'create_date' => $this->getHelper('Data')->getCurrentGmtDate()
            ];
        }

        $mainOrderItemTable = $this->activeRecordFactory->getObject('Order\Item')->getResource()->getMainTable();
        $componentOrderItemTable = $this->activeRecordFactory
                                        ->getObject(ucfirst($componentName).'\Order\Item')
                                        ->getResource()->getMainTable();

        $queryStmt = $coreResource->getConnection()->select()
            ->from(['main_table' => $mainOrderItemTable])
            ->joinInner(
                ['second_table' => $componentOrderItemTable],
                'second_table.order_item_id = main_table.id'
            )
            ->where('main_table.order_id IN (?)', $componentOrdersIds)
            ->query();

        $orderItemsIds = [];

        while ($itemRow = $queryStmt->fetch()) {
            if (!isset($insertsData[$itemRow['order_id']])) {
                continue;
            }

            $insertsData[$itemRow['order_id']]['data']['order_item_data'][$itemRow['id']] = $itemRow;
            $orderItemsIds[] = (int)$itemRow['id'];
        }

        if (empty($insertsData)) {
            return;
        }

        foreach ($insertsData as $key => &$data) {
            $data['data'] = $this->getHelper('Data')->jsonEncode($data['data']);
        }
        unset($data);

        $connWrite = $coreResource->getConnection();
        $connWrite->insertMultiple(
            $this->activeRecordFactory->getObject('ArchivedEntity')->getResource()->getMainTable(),
            $insertsData
        );

        $connWrite->delete($mainOrderTable, ['id IN (?)' => $componentOrdersIds]);
        $connWrite->delete($componentOrderTable, ['order_id IN (?)' => $componentOrdersIds]);

        $connWrite->delete($mainOrderItemTable, ['id IN (?)' => $orderItemsIds]);
        $connWrite->delete($componentOrderItemTable, ['order_item_id IN (?)' => $orderItemsIds]);
    }

    //########################################
}
