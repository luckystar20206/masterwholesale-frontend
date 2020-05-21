<?php


namespace ModernRetail\Import\Controller\Remote;



class Kshipping extends \Magento\Framework\App\Action\Action
{
   

	public function getPath(){
		return "/var/www/vhosts/kevinscatalog/magento/pub/mr_import";
	}

    public function __construct(  \Magento\Framework\App\Action\Context $context, \ModernRetail\Import\Model\Xml $import, \ModernRetail\Import\Helper\Data $helper, \Magento\Catalog\Model\Product $productModel)
    {
        $this->import = $import;
        $this->helper = $helper;
		$this->productModel = $productModel;
        parent::__construct($context);
    }



public function _readCsvFile($file){
		$row = 0;
		$keys = array();
		$_data = array();
		if (($handle = fopen($file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    	$row++; 
		    	if ($row==1){
		    		$keys = (array_values($data));
					continue;
		    	}
				
		        $_data[] = array_combine($keys, $data);
		    }
		    fclose($handle);
		}
		return $_data;
	}

	
 
    public function execute()
    {
    	
		$data = $this->_readCsvFile($this->getPath()."/shipping2.csv");
	//	d($data);
	    
		
     
	    
	  
	   foreach($data as $_data){
	   		 $this->productModel->reset(); 
		   
			 $magentoProduct = $this->productModel->loadByAttribute("sku",$_data['ITEM #']);
			 if ($magentoProduct && $magentoProduct->getId()){
			 	$id = $magentoProduct->getId();
			 	
				
				
				
				
				$shipperhq_poss_boxes = $_data['Dimensions']; 
				if ($shipperhq_poss_boxes){
					$shipperhq_poss_boxes = $this->helper->getAttributeValue('shipperhq_poss_boxes',$shipperhq_poss_boxes);
					$magentoProduct->setData('shipperhq_poss_boxes',$shipperhq_poss_boxes);
					$magentoProduct->getResource()->saveAttribute($magentoProduct, 'shipperhq_poss_boxes');
				}  
				
				
				$shippingGroup = $_data['Shipping Group'];
				if ($shippingGroup){
					$shippingGroup =  $this->helper->getAttributeValue('shipperhq_shipping_group',$shippingGroup);
					$magentoProduct->setData('shipperhq_shipping_group',$shippingGroup);
					
					$magentoProduct->getResource()->saveAttribute($magentoProduct, 'shipperhq_shipping_group');
				} 
				
				
				
				//d($shippingGroup);
			 	//$magentoProduct = $this->productModel->load($id);
				//$magentoProduct->setMetaDescription($_data['METADESC']);
				//$magentoProduct->setMetaTitle($_data['METATITLE']); 
				//$magentoProduct->save();
				  
				 // d("DONE");
				  //$magentoProduct->getResource()->saveAttribute($magentoProduct, 'meta_description'); 
			 }else {
			 	dd($_data['ITEM #']." NOT FOUND");
			 }
	   }

    }
}