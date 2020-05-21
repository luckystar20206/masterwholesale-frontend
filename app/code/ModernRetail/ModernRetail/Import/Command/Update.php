<?php
namespace ModernRetail\Import\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends Command
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
        $this->setName('kevinupdate');
        $this->setDescription('Run kevin task');

        parent::configure();
    }
	
	
	
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	d(__LINE__);
    }
}