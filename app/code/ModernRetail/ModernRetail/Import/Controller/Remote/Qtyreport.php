<?php
namespace ModernRetail\Import\Controller\Remote;

class Qtyreport extends \ModernRetail\Import\Controller\RemoteAbstract
{


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\Config $eav_config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $import, $helper, $resource,$storeManager);
        $this->eav_config = $eav_config;
    }

    public function execute()
    {
        $resource = $this->resource;


        //d($this->helper->getCurrentStoreId());

        $_entity_id = $this->helper->getEntityIdFieldName();

        $priceAttribute = $this->eav_config->getAttribute('catalog_product',"price");
        $specialPriceAttribute = $this->eav_config->getAttribute('catalog_product',    "special_price");


        $store_id = $this->helper->getCurrentStoreId();


        $sql = "SELECT
		p.$_entity_id,
		p.sku,
		coalesce(rp.value,rp0.value,0) as price,
		coalesce(sp.value,sp0.value,0) as special_price,
		i.qty
		FROM ".$resource->getTableName('catalog_product_entity')." p
		left outer join ".$resource->getTableName('cataloginventory_stock_item')." i
		on i.product_id = p.$_entity_id 
		
		left join ".$resource->getTableName('catalog_product_entity_decimal')." rp0
		on rp0.$_entity_id = p.$_entity_id and rp0.attribute_id = ".$priceAttribute->getAttributeId()."  and rp0.store_id = 0
		
		left  join ".$resource->getTableName('catalog_product_entity_decimal')." rp
		on rp.$_entity_id = p.$_entity_id and rp.attribute_id = ".$priceAttribute->getAttributeId()."  and rp.store_id = $store_id
		
		
		left outer join ".$resource->getTableName('catalog_product_entity_decimal')." sp0
		on sp0.$_entity_id = p.$_entity_id and sp0.attribute_id = ".$specialPriceAttribute->getAttributeId()."   and sp0.store_id = 0
		
		left outer join ".$resource->getTableName('catalog_product_entity_decimal')." sp
		on sp.$_entity_id = p.$_entity_id and sp.attribute_id = ".$specialPriceAttribute->getAttributeId()."  and sp.store_id = $store_id
		
		where  
		p.type_id = 'simple'";



        $connection = $resource->getConnection('core_read');
        $rows = $connection->fetchAll($sql);

        /**
         * Store Specified values
         */






        $csv = array("entity_id,sku,price,special_price,qty");
        foreach($rows as $row){
            $csv[] ='"'.$row[$_entity_id]
                .'","'.$row['sku'].'"'
                .',"'.$row['price'].'"'
                .',"'.$row['special_price'].'"'
                .',"'.$row['qty'].'"';
        }

        header("Content-Disposition: attachment; filename=qtyreport-".date("m-d-Y").".csv");
        header('Content-Type: text/csv');
        die(join("\n",$csv));
    }
}