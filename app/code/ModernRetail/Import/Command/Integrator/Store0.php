<?php
namespace ModernRetail\Import\Command\Integrator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Store0 extends Command
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
        $this->setName('integrator:store0');
        $this->setDescription('Fix store1 and store 0');
 		//$this->setDefinition($this->getInputList());
        parent::configure();
    }
	


    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$this->fixProductEntityTables( $output);
    }
	
	
	protected function fixProductEntityTables($output){
		
		$tables = ['catalog_product_entity_varchar','catalog_product_entity_decimal','catalog_product_entity_datetime','catalog_product_entity_text','catalog_product_entity_int'];
		$readConnection = $this->resource->getConnection('core_read');
		$writeConnection = $this->resource->getConnection('core_write');
	
		foreach($tables as $table){
			$table = $this->resource->getTableName($table);
			 
			$sql = "delete store_0 from $table as store_0
					left join $table as store_1
					on store_0.attribute_id = store_1.attribute_id and store_0.entity_id = store_1.entity_id and store_1.store_id = 1
					where store_1.value is not null and store_0.store_id = 0";
			$writeConnection->query($sql);
			
			$sql = "update ignore $table set store_id = 0 where store_id = 1";
			$writeConnection->query($sql);
			
			$output->writeLn('Fixed '.$table);
			
		}
		   
		
		$table = $this->resource->getTableName('catalog_product_entity_media_gallery_value');
		
		$sql = "select * from $table where store_id = 0";
		$all = $readConnection->query($sql)->fetchAll(); 
	
		
		foreach($all as $row){
			$sql = "select * from $table where store_id = 1 and value_id = ".$row['value_id'];
			$result = $readConnection->query($sql)->fetchAll();
			if (count($result)==0) continue;
			$store_1 = $result[0];
			     
			if ($row['label']!=$store_1['label']){
				$sql = "update $table set label = '".addslashes($store_1['label'])."' where value_id = ".$row['value_id']." and store_id = 0";
				$writeConnection->query($sql);
			}
			
			$sql = "delete from $table where value_id = ".$row['value_id']." and store_id = 1"; 
			$writeConnection->query($sql);
		}
		
		
		$sql = "update $table set store_id = 0 where store_id = 1";  
		$writeConnection->query($sql);
		
	}
	 
	
	
	
	
}