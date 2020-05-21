<?php

namespace ModernRetail\ApiOrders\Plugin\Sales;

class Grid
{
    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'mr_api_queue';
    protected $types = [
        'order', 'invoice', 'shipment', 'creditmemo'
    ];

    protected $tables = [
        'sales_order_grid',
        'sales_invoice_grid',
        'sales_shipment_grid',
        'sales_creditmemo_grid'
    ];

    public function afterSearch($intercepter, $collection)
    {
        $type = 'order';
        $tableExists = false;
        foreach ($this->types as $item) {
            if (strpos($collection->getMainTable(), $item) !== false) {
                $type = $item;
                break;
            }
        }
        $connection = $collection->getConnection();

        foreach ($this->tables as $table) {
            if ($collection->getMainTable() === $connection->getTableName($table)) {
                $tableExists = true;
                break;
            }
        }

        if ($tableExists) {
            $leftJoinTableName = $connection->getTableName(self::$leftJoinTable);
            $column = 'maq.status';
            $collection
                ->getSelect()
                ->joinLeft(
                    ['maq' => $leftJoinTableName],
                    "maq.entity_id = main_table.entity_id AND maq.type = '$type'",
                    [
                        'mr_api_sync_status' => "$column",
                        'sent_to_mr_api_queue' => "$column"
                    ]
                );

            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

            $where = str_replace('`mr_api_sync_status`', $column, $where);
            $where = str_replace("$column = 'no'", "$column IS NULL", $where);

            $where = str_replace("`sent_to_mr_api_queue` = 'no'", "($column != 'complete' OR $column IS NULL)", $where);
            $where = str_replace('`sent_to_mr_api_queue`', $column, $where);

            if (is_array($where)) {
                if (strpos(reset($where), '`main_table`.`entity_id`') === false) {
                    $where = str_replace('`entity_id`', "main_table.entity_id", $where);
                }
            } else if (strpos($where, '`main_table`.`entity_id`') === false) {
                $where = str_replace('`entity_id`', "main_table.entity_id", $where);
            }
//            $where = str_replace('`entity_id`', "main_table.entity_id", $where);
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);

//            echo $collection->getSelect()->__toString();die;
        }

        return $collection;
    }
}