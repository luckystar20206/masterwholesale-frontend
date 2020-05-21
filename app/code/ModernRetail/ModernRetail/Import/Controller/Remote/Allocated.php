<?php
namespace ModernRetail\Import\Controller\Remote;

class Allocated extends \ModernRetail\Import\Controller\RemoteAbstract
{



    public function execute()
    {
    	$_entity_id = $this->helper->getEntityIdFieldName();
		
		
		$sales_order = $this->helper->getTableName('sales_order');
		$sales_order_item = $this->helper->getTableName('sales_order_item');
		$catalog_product_entity_varchar = $this->helper->getTableName('catalog_product_entity_varchar');

        $sql = "SELECT sku, sum(qty_ordered-qty_shipped-qty_canceled-qty_refunded) as allocated
                    FROM $sales_order_item
                    group by sku
                    having sum(qty_ordered-qty_shipped-qty_canceled-qty_refunded) > 0";
		
		$sales_order = $this->helper->getTableName('sales_order');
		$sales_order_item = $this->helper->getTableName('sales_order_item');
		$catalog_product_entity_varchar = $this->helper->getTableName('catalog_product_entity_varchar');
		
					
					
		
		$sql = "SELECT `main_table`.`sku`, `main_table`.`created_at`, `main_table`.`status`, `main_table`.`alu`, SUM(main_table.allocated) AS `allocated`, GROUP_CONCAT(CONCAT_WS('_',order_id,increment_id,created_at),',') AS `order_ids` FROM 
			 (select 
				(
					if (
						qty_shipped > 0, 
						(
							qty_ordered - qty_shipped - qty_canceled
						), 
						(
							qty_ordered - qty_shipped - qty_canceled - qty_refunded
						)
					)
				) as allocated, 
				$catalog_product_entity_varchar.value as alu,
				order_id, 
				status,
				$sales_order.increment_id,
				$sales_order.created_at,
				sku
			from 
				$sales_order_item 
				left join $sales_order on $sales_order_item.order_id = $sales_order.$_entity_id
				left join $catalog_product_entity_varchar on $sales_order_item.product_id = $catalog_product_entity_varchar.$_entity_id and attribute_id = 133
			where 
				 (
					
					product_type = 'configurable' 
					OR (
						product_type = 'simple' 
						AND parent_item_id is null
					)
				)
			) AS `main_table` WHERE (allocated > 0) AND (status not in ('canceled','complete')) GROUP BY `sku` ORDER BY `created_at` desc";	
					
					
					

        $connection = $this->resource->getConnection('core_read');
        $rows = $connection->fetchAll($sql);

        $csv = array("sku,allocated");
        foreach($rows as $row){
            $csv[] =
                '"'.$row['sku'].'"'
                .',"'.$row['allocated'].'"';
        }

        header("Content-Disposition: attachment; filename=allocated-".date("m-d-Y").".csv");
        header('Content-Type: text/csv');
        die(join("\n",$csv));
    }
}