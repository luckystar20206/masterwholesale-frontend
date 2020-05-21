<?php
namespace ModernRetail\Import\Command\Integrator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command
{
	
	
	public function __construct(
			\Magento\Framework\App\State $state, 
			\ModernRetail\Import\Helper\Data $dataHelper,
			\Magento\Framework\Event\ManagerInterface $eventManager
			){
		//$state->setAreaCode('adminhtml');
		$this->eventManager = $eventManager; 
		$this->helper = $dataHelper;
		
		parent::__construct(); 
	}
	
    protected function configure()
    {
        $this->setName('integrator:run');
        $this->setDescription('Run Integrator bucket & file');
 		$this->setDefinition($this->getInputList());
        parent::configure();
    }
	


    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$bucket = $input->getArgument("bucket");
		$file = $input->getArgument('file');
		
		if (!$file && strpos($bucket, "/")){
			list($file,$bucket) = explode($bucket);
		}
		$this->eventManager->dispatch("integrator_run_file",array("bucket"=>$bucket,"file"=>$file,"debug"=>true));
		
    }
	 
	
	
    public function getInputList() {
        $inputList = [];
        $inputList[] = new InputArgument('bucket', InputArgument::OPTIONAL, 'Bucket Folder', null);
        $inputList[] = new InputArgument('file', InputArgument::OPTIONAL, 'File', null);
     #   $inputList[] = new InputOption('mylimit', null, InputOption::VALUE_OPTIONAL, 'Collection Limit as Option', 100);
        return $inputList;
    }
	
	
}