<?php 

namespace ModernRetail\Import\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Fixurls extends Command
{
	 
	 
	public function __construct(\Magento\Cms\Model\Page $cmsPage,  \Magento\Framework\App\State $state, \Magento\Catalog\Model\Product  $product, \Magento\Catalog\Model\Category  $category,    \Magento\Framework\Event\ManagerInterface $eventManager){
		$this->cmsPage = $cmsPage;
		 $this->eventManager = $eventManager;
		 $this->product = $product;
		 $this->category = $category;
		  $state->setAreaCode('adminhtml');
		parent::__construct();
	}
	
    protected function configure()
    {
        $this->setName('fixurls');
        $this->setDescription('Run url reindex task');

        parent::configure();
    }
	
	


    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$sql = "select tt.*  from (
			select count(value) as rpt,value from catalog_product_entity_varchar where attribute_id = 115  GROUP by value ORDER BY count(value) DESC ) as tt where tt.rpt > 1";


		
		
    	d(__LINE__);
    }
}
