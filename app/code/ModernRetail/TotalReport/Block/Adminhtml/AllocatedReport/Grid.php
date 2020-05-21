<?php
namespace ModernRetail\TotalReport\Block\Adminhtml\AllocatedReport;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\Order\Item $salesOrderItem,
        array $data = array()
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->setId('allocatedReportGrid');
        //$this->setDefaultSort('powercart_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);

        $this->salesOrderItem = $salesOrderItem;
    }



    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(100000);
        $this->getCollection()->setCurPage(1);
    }



    protected function _prepareCollection()
    {


        $collection = $this->salesOrderItem->getCollection();

        // $collection->getSelect()->group('sku');
        // $collection->join(array("sales_order"),"main_table.order_id = sales_order.entity_id",array("increment_id"));
        // $collection->getSelect()->columns(
            // array('sku',
                // 'allocated'=>new \Zend_Db_Expr('sum(qty_ordered-qty_shipped-qty_canceled-qty_refunded)'),
                // 'order_ids'=>new \Zend_Db_Expr('GROUP_CONCAT(CONCAT_WS(":",order_id,increment_id),",")'),
                // 'orders_allocated'=>new \Zend_Db_Expr('GROUP_CONCAT(qty_ordered-qty_shipped-qty_canceled-qty_refunded)')
            // ));
        // $collection->getSelect()->having('sum(qty_ordered-qty_shipped-qty_canceled-qty_refunded) > 0');
        // //die($collection->getSelect()->__toString());
//         
        
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('ModernRetail\Import\Helper\Data');
		
        
		$sales_order = $helper->getTableName('sales_order');
		$sales_order_item = $helper->getTableName('sales_order_item');
		$catalog_product_entity_varchar = $helper->getTableName('catalog_product_entity_varchar');
        
        $collection->getSelect()->reset("from");
		$collection->getSelect()->reset("where"); 
		$collection->getSelect()->reset("columns");
	  
		$collection->getSelect()->from(array("main_table"=>new \Zend_Db_Expr("
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
				left join $sales_order on $sales_order_item.order_id = $sales_order.entity_id
				left join $catalog_product_entity_varchar on $sales_order_item.product_id = $catalog_product_entity_varchar.entity_id and attribute_id = 133
			where 
				 (
					
					product_type = 'configurable' 
					OR (
						product_type = 'simple' 
						AND parent_item_id is null
					)
				)
			)")),
			 
				array(
					"sku",
					"created_at", 
					"status",
					"alu", 
					"allocated"=> new \Zend_Db_Expr('SUM(main_table.allocated)'),
					"order_ids"=> new \Zend_Db_Expr('GROUP_CONCAT(CONCAT_WS("_",order_id,increment_id),",")'),
				)
				
			);
			
			$collection->getSelect()->where("allocated > 0");
			$collection->getSelect()->where("status not in ('canceled','complete')");
			$collection->getSelect()->order("created_at desc");
			$collection->getSelect()->group("sku");  
        
        
        
        $this->setCollection($collection);


        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {

        $this->addColumn('sku', array(
            'header'    => 'SKU',
            'align'     =>'left',
            'index'     => 'sku',
        ));

        $this->addColumn('allocated', array(
            'header'    => 'Allocated',
            'align'     =>'left',
            'index'     => 'allocated',
            'sortable'=>false,
            'filter'=>false,
        ));


        $this->addColumn('order_ids', array(
            'header'    =>'Order Ids',
            'align'     =>'left',
            'sortable'=>false,
            'filter'=>false,
            'index'     => 'order_ids',
            'renderer'  => "ModernRetail\TotalReport\Block\Adminhtml\AllocatedReport\Renderer\OrderIds"
        ));
        //$this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));

        return parent::_prepareColumns();
    }

}