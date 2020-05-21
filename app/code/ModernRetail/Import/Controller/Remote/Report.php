<?php
namespace ModernRetail\Import\Controller\Remote;

class Report extends \ModernRetail\Import\Controller\RemoteAbstract
{


    public function execute()
    {
        $resource = $this->resource;
        $bucket = date("m-d-Y");

		$_entity_id = $this->helper->getEntityIdFieldName();

        $connection = $this->resource->getConnection('core_read');
        $sql  = "select $_entity_id as entityid,sku from ".$resource->getTableName("catalog_product_entity")." where type_id = 'simple'";
        $rows = $connection->fetchAll($sql);

        $csv = array("entity_id,sku");
        foreach($rows as $row){
            $csv[] ='"'.$row['entityid'].'","'.$row['sku'].'"';
        }
        file_put_contents( $this->helper->getPath().DS."..".DS."reports".DS."report-".date("m-d-Y").".csv",join("\n",$csv));
        die($_SERVER['SERVER_NAME']."/mr_import".DS."reports".DS."report-".$bucket.".csv");
    }
}