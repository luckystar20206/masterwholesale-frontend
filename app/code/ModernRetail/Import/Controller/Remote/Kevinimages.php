<?php


namespace ModernRetail\Import\Controller\Remote;



class Kevinimages extends \Magento\Framework\App\Action\Action
{


	public function getPath(){
		return "/var/www/vhosts/kevinscatalog/magento/pub/media";
	}

    public function __construct(  \Magento\Framework\App\Action\Context $context, \ModernRetail\Import\Model\Xml $import, \ModernRetail\Import\Helper\Data $helper, \Magento\Catalog\Model\Product $productModel)
    {
        $this->import = $import;
        $this->helper = $helper;
		$this->productModel = $productModel;
        parent::__construct($context);
    }



	
 
    public function execute()
    { 

		$fileName = $this->helper->getPath()."/images-1.xml"; 
		
       $xml = simplexml_load_file($fileName);
	   
	   $xmlImages = $xml->xpath("ProductImage");
	   
	   foreach($xmlImages as $xmlImage){
	   		 $this->productModel->reset();
			 $xmlImage = (array)$xmlImage;
		    $magentoProduct = $this->productModel->loadByAttribute("sku",$xmlImage['sku']);
            if (!$magentoProduct){
                dd("PRODUCT with integrationID = ".$xmlImage['sku']." not found");
                
                continue;
            } 
            $magentoProduct = $this->productModel->load($magentoProduct->getId());
			$image = $this->downloadImage($xmlImage['ImageURL']);  
			//d($this->helper->getPath().$image); 
			//$addedImage = $magentoProduct->addImageToMediaGallery($this->helper->getPath().$image, array('thumbnail','image','small_image'), false,false);
			
			
			    $attributes = $magentoProduct->getTypeInstance()->getSetAttributes($magentoProduct); 
		        $mediaGalleryAttribute = $attributes['media_gallery'];
			//dd($this->getPath().$image); 
		        /* @var $mediaGalleryAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
		        //d($magentoProduct->getData('media_gallery'));
		        
		        $position = $xmlImage['imageOrder'];
				if ($position!=0){
					$type = "media_image";
				}else {
					$type = array("image","base_image","thumbnail");
				}
				
				$_fname = explode("/",$image);
				$fileName = array_pop($_fname);
				$images = $magentoProduct->getData('media_gallery')['images'];
				$needSkip = false;
				foreach($images as $_image){
					if (strpos($_image['file'], $fileName)!==false){
						$needSkip = true;	
						continue;
						
					}
				} 
				if ($needSkip===true) continue;
				
		      	$im =   $mediaGalleryAttribute->getBackend()->addImage($magentoProduct, $this->getPath().$image, $type, false,false);
				$mediaGalleryAttribute->getBackend()->updateImage($magentoProduct,$im,array('label'=>'yandex','position'=>455,'label_default'=>'yandexаааа')); 
				
				$magentoProduct->save(); 
		    	
			    //return $this;
				
	   }

    }
}