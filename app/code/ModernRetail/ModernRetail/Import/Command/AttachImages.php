<?php
namespace ModernRetail\Import\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AttachImages extends Command
{
	
	
	public function __construct(\Magento\Cms\Model\Page $cmsPage,   \Magento\Framework\App\ResourceConnection $resource, \Magento\Framework\App\State $state, \Magento\Catalog\Model\Product  $product, \Magento\Catalog\Model\Category  $category,    \Magento\Framework\Event\ManagerInterface $eventManager,\Magento\Framework\App\Filesystem\DirectoryList $directory_list){
		$this->cmsPage = $cmsPage;
		$this->resource =  $resource;	
		 $this->eventManager = $eventManager;
		 $this->product = $product;
		 $this->category = $category;
		
		$this->directory_list = $directory_list;
		parent::__construct();
	}
	
    protected function configure()
    {
        $this->setName('attachimages');
        $this->setDescription('');

        parent::configure();
    }
	
	

    protected function execute(InputInterface $input, OutputInterface $output)
    {
   	
		$media = $this->directory_list->getPath('media');
		$import = $media."/import/";
		$filesDir  = $import;
		$files = glob($filesDir."*"."{.,_}*{JPEG,jpg,jpeg,JPG,png}",GLOB_BRACE);
		$skus = array();
		foreach($files as $file){
			$fileName = @array_pop(explode("/",$file));
			$image = @array_shift(explode(".",$fileName)); 
			$sku = @array_shift(explode("_",$image));
			$skus[$sku] = 0 ; 
		} 
		
		
		foreach($skus as $sku=>$v){
			$this->eventManager->dispatch('integrator_attach_image', array('sku' => $sku ));
		}
	
    }
}