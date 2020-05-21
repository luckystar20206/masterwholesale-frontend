<?php
namespace ModernRetail\Import\Command\Integrator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StockStatus extends Command
{
	public function __construct(
			\Magento\Framework\App\State $state, 
			\ModernRetail\Import\Helper\Data $dataHelper,
			\Magento\Framework\Event\ManagerInterface $eventManager,
			 \Magento\Framework\App\ResourceConnection $resource
			){
		//$state->setAreaCode('adminhtml');
		$this->eventManager = $eventManager; 
		$this->helper = $dataHelper;
		$this->resource = $resource;
		
		parent::__construct(); 
	}
	
    protected function configure()
    {
        $this->setName('integrator:stock_status');
        $this->setDescription('Fix stock_statuses'); 
 		//$this->setDefinition($this->getInputList());
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
 		$this->_fixStore1($input, $output);
    }
	
	
	protected function _fixStore1(InputInterface $input, OutputInterface $output){

    	$readConnection = $this->resource->getConnection('core_read'); 
	    $writeConnection = $this->resource->getConnection('core_write'); 
		$cataloginventory_stock_status = $this->resource->getTableName('cataloginventory_stock_status');
		$cataloginventory_stock_item = $this->resource->getTableName('cataloginventory_stock_item');
		$catalog_product_entity = $this->resource->getTableName('catalog_product_entity');
		
		$sql = "select * from $cataloginventory_stock_status where product_id in (select  product_id from  $cataloginventory_stock_status where website_id = 1 ) and website_id = 0";
		$all = $readConnection->query($sql)->fetchAll();
		
		
		foreach($all as $data){
			$sql = "delete from $cataloginventory_stock_status where product_id = ".$data['product_id']." and website_id = 0";
			$writeConnection->query($sql);  
			$sql = " update $cataloginventory_stock_status set website_id = 0 where product_id = ".$data['product_id'];
			$writeConnection->query($sql);   
		}
		
		$sql = "update  $cataloginventory_stock_status
			left join  $catalog_product_entity on  $cataloginventory_stock_status.product_id = $catalog_product_entity.entity_id
			set stock_status = 1
			  where type_id = 'simple' and qty > 0 and stock_status=0";
		$writeConnection->query($sql);    
		
		$sql = "update $cataloginventory_stock_item 
			    left join  $catalog_product_entity on  $cataloginventory_stock_item.product_id = $catalog_product_entity.entity_id
			    set is_in_stock = 1
			    where type_id = 'simple' and qty > 0 and is_in_stock=0";
		$writeConnection->query($sql);      
		
	}

	
	
	
}