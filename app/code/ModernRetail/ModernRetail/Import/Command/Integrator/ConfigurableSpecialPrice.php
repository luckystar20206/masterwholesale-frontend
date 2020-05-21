<?php
namespace ModernRetail\Import\Command\Integrator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
 
class ConfigurableSpecialPrice extends Command
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
        $this->setName('integrator:configurable_special_price');
        $this->setDescription('Remove special_price attribute from configurable product');
 		//$this->setDefinition($this->getInputList());
        parent::configure();
    }
	


    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	
    }
	
	
	
	
	
}