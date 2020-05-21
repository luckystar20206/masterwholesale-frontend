<?php
namespace ModernRetail\Import\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class FinishImport  implements ObserverInterface{



    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resource = $objectManager->create("\Magento\Framework\App\ResourceConnection");
        $db = $this->resource->getConnection('core_write');
        $cataloginventory_stock_status = $this->resource->getTableName('cataloginventory_stock_status');
        $cataloginventory_stock_item = $this->resource->getTableName('cataloginventory_stock_item');
        $catalog_product_entity_int = $this->resource->getTableName('catalog_product_entity_int');
        $eav_attribute = $this->resource->getTableName('eav_attribute');

        $sql = "insert ignore into $cataloginventory_stock_status
                    select _sitem.product_id,_sitem.website_id,_sitem.stock_id, _sitem.qty, _sitem.is_in_stock from $cataloginventory_stock_item  as _sitem
                    left join $catalog_product_entity_int as _status on  _status.attribute_id = (select attribute_id from $eav_attribute where attribute_code = 'status' and entity_type_id = 4) and _sitem.product_id = _status.entity_id
                    where product_id not in (select product_id from $cataloginventory_stock_status ) and _status.value = 1 order by product_id desc";

        //try {
        $db->query($sql);
        //}catch(\Exception $ex){}
    }

}