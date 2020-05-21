<?php
namespace ModernRetail\Import\Controller\Remote;

class Images extends \Magento\Framework\App\Action\Action
{
	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\App\Filesystem\DirectoryList $directory_list){
		$this->directory_list = $directory_list;
		$this->eventManager = $context->getEventManager(); 
		parent::__construct($context);
	}

    public function execute()
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