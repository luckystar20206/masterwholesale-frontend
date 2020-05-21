<?php
namespace ModernRetail\Import\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class AttachImage  implements ObserverInterface{

    public $scopeConfig;
    public $import;
    public $session ;
    public $resourceConfig;
    public $helper;
    public $cacheListType;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Session $session,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $dataHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
         \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
         \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
         \Magento\Catalog\Model\Product\Gallery\Processor $mediaGalleryProcessor 

    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->helper = $dataHelper;
        $this->import = $import;
        $this->resourceConfig = $resourceConfig;
        $this->cacheListType = $cacheTypeList;
			$this->directory_list = $directory_list;
		$this->productRepository = $productRepository;
				$this->mediaGalleryProcessor = $mediaGalleryProcessor; 
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
    	try {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$media = $this->directory_list->getPath('media');
		$import = $media."/import/";
		$filesDir  = $import;
		
		
		$sku = $observer->getSku();
		
		if ($sku){
			$product = $this->productRepository->get($sku,true,0);
		}
		if (!$product ||  !$product->getId()) return false;
		
		
		$sku = $product->getSku();
		
		
		$files = glob($filesDir.$sku."{.,_}*{JPEG,jpg,jpeg,JPG,png}",GLOB_BRACE);
		
		
		$images = array();
		foreach($files as $file){
			$name = @array_pop(explode("/",$file));
			$images[$name] = $file;
		}
		ksort($images);
		if (count($images)==0) return false;
		
		/**
		 * First image
		 */
		// $mainImage = array_shift($images);
		$importImages = $images;
		
		 
		$magentoProduct = $product;
	    $attributes = $magentoProduct->getTypeInstance()->getSetAttributes($magentoProduct); 
		
        $mediaGalleryAttribute = $attributes['media_gallery'];
		$existImages = $magentoProduct->getData('media_gallery')['images'];
		$i=0;
		foreach($importImages as $importImage){
			$type = "media_image";
			if ($i==0){
				$type = array("image","base_image","thumbnail","small_image");
			}
			$fileName = @array_pop(explode("/",$importImage));
			$needSkip = false;
			foreach($existImages as $_image){
				if (strpos($_image['file'], $fileName)!==false){
					$needSkip = true;	
					dd($fileName." already exist");
					continue;
					
				}
			} 
			if ($needSkip===true){
				continue;
			}
			$label = "";
			if (file_exists($importImage)){
				try {
			  		$im =   $this->mediaGalleryProcessor->addImage($magentoProduct, $importImage, $type, false,false);
					$this->mediaGalleryProcessor->updateImage($magentoProduct,$im,array('label'=>$label,'position'=>$i,'label_default'=>$label)); 
				$magentoProduct->save(); 
				} catch(\Exception $ex){
					dd($ex->getMessage()." - [$fileName] FOR PRODUCT with integrationID = ".$sku."");
				}
			}
			
			$i++;
		}
		
		
		}catch (\Exception $ex){
			dd("$sku - ".$ex->getMessage());
		}
		 
		
	}

}