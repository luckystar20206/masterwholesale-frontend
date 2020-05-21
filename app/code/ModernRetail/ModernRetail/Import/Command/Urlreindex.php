<?php
namespace ModernRetail\Import\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Urlreindex extends Command
{
	
	
	public function __construct(\Magento\Cms\Model\Page $cmsPage,   \Magento\Framework\App\ResourceConnection $resource, \Magento\Framework\App\State $state, \Magento\Catalog\Model\Product  $product, \Magento\Catalog\Model\Category  $category,    \Magento\Framework\Event\ManagerInterface $eventManager){
		$this->cmsPage = $cmsPage;
		$this->resource =  $resource;	
		 $this->eventManager = $eventManager;
		 $this->product = $product;
		 $this->category = $category;
		  $state->setAreaCode('adminhtml');
		parent::__construct();
	}
	
    protected function configure()
    {
        $this->setName('urlreindex');
        $this->setDescription('Run url reindex task');

        parent::configure();
    }
	
	
	protected function _fixUrls($output){
		 //$this->connection = $this->resource->getConnection('core_write');
		 
		  $readConnection = $this->resource->getConnection('core_read');
		  $writeConnection = $this->resource->getConnection('core_write');
			$sql = "select tt.*  from (
			select count(value) as rpt,value from catalog_product_entity_varchar where attribute_id = 115  GROUP by value ORDER BY count(value) DESC ) as tt where tt.rpt > 1";
		             $result = $readConnection->query($sql)->fetchAll();
				
		 foreach($result as $res){
		 	$sql = 'SET @increment=0;UPDATE catalog_product_entity_varchar  SET value = (SELECT IF ((@increment:=@increment+1)>1,CONCAT(value,"-",@increment),value) AS new_value) where value = "'.$res['value'].'";';
				$output->writeln($sql);
		 }
		 // $result = $this->connection->fetchAll($sql);
		 // ci($result);
// 		  
		 
		 die('');
			//d($sql);
	}
	
	protected function _reindexCmsPages($output){
		
		$output->writeln("---- Starting reindex CMS Pages");
		$collection  = $this->cmsPage->getCollection();
		$total = $collection->count();
		$i=0;
		foreach($collection as $item){
			$i++;	
			$item->setOrigData('identifier',time());
			$this->eventManager->dispatch("cms_page_save_after",['object'=>$item]);
			$output->writeln("\t $i \ $total : [".$item->getTitle()."] reindexed");
		}
		
		$output->writeln("---- CMS Pages reindex sucessfully");
	}

	protected function _reindexCatalogCategories($output){
		
		$output->writeln("---- Starting reindex Categories");
		$collection  = $this->category->getCollection();
		$collection->addAttributeToSelect(["url_key","name"]);
		$collection->addFieldToFilter('entity_id',1759);
		$total = $collection->count();
		//d($total); 
		$output->writeln("$total total categories");
		$i=0;
		foreach($collection as $item){
			$i++;
			//ci($item);
			//$this->category->reset();
			//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			//$item =   $objectManager->create('Magento\Catalog\Model\Category')->load($item->getId());
			
			$item->setOrigData('url_key',time());
			
			//d($item->getUrlKey());
		 	
				$this->eventManager->dispatch("catalog_category_save_before",['category'=>$item]);
				$this->eventManager->dispatch("catalog_category_save_after",['category'=>$item]);
			
			$output->writeln("\t $i \ $total : [".$item->getName()."] reindexed");
		}
		
		$output->writeln("---- Categories reindex sucessfully");
	}
	
	
		protected function _reindexCatalogProducts($output){
		
		$output->writeln("---- Starting reindex Products");
		$collection  = $this->product->getCollection();
		$collection->addAttributeToSelect(["url_key","name","url_path"]);
		$collection->addAttributeToFilter("visibility",4);
			$collection->addFieldToFilter('entity_id',31195); 
		$total = $collection->count();
		//d($total); 
		$output->writeln("$total total categories");
		$i=0;
		foreach($collection as $item){
			$i++;
			//ci($item);
			//$this->category->reset();
			//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			//$item =   $objectManager->create('Magento\Catalog\Model\Category')->load($item->getId());
			
			$item->setOrigData('url_key',time());
			
			//d($item->getUrlKey());
		 	
				$this->eventManager->dispatch("catalog_product_save_before",['product'=>$item,'object'=>$item]);
				$this->eventManager->dispatch("catalog_product_save_after",['product'=>$item,'object'=>$item]);
			
			$output->writeln("\t $i \ $total : [".$item->getName()."] reindexed");
		}
		
		$output->writeln("---- Product reindex sucessfully");
	}


    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	
			//$this->_fixUrls($output);
    	 
    	//$this->_reindexCatalogCategories($output);
		$this->_reindexCatalogProducts($output);
    	//$this->_reindexCmsPages($output);
    }
}