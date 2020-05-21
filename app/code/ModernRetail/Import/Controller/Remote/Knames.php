<?php


namespace ModernRetail\Import\Controller\Remote;


 
class Knames extends \Magento\Framework\App\Action\Action
{
   

	public function getPath(){
		return "/var/www/vhosts/kevinscatalog/magento/pub/media";
	}

    public function __construct(  \Magento\Framework\App\Action\Context $context, \ModernRetail\Import\Model\Xml $import, \ModernRetail\Import\Helper\Data $helper, \Magento\Catalog\Model\Product $productModel,\Magento\Catalog\Model\Product\Url $urlModel)
    {
        $this->import = $import;
        $this->helper = $helper;
		$this->urlModel = $urlModel;
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
				
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();    	
		
		
		
    	
		$data = $this->_readCsvFile($this->getPath()."/names.csv");
	//	d($data);
	    
		
     
	    $total =  count($data);
	   $i=0;
	   foreach($data as $_data){
	   	$i++;
	   		 //$this->productModel->reset(); 
			 //$magentoProduct = $this->productModel->load($_data['entity_id']);
			 //d($_data); 
			// if ($magentoProduct && $magentoProduct->getId()){
			 	$newUrlkey = $this->urlModel->formatUrlKey($_data['name']);
				
				$entity_id = $_data['entity_id'];
				// name 70 
				// visibility 95
				// url_ 115
				//ci($connection);
				
				
				$_data['name'] = addslashes($_data['name']);
				
				$sql = "update catalog_product_entity_varchar as _varchar 
						left join catalog_product_entity_int as _visibility on  _varchar.entity_id = _visibility.entity_id  and _visibility.attribute_id = 96 set _varchar.value = \"".$_data['name']."\"
						where _varchar.entity_id = $entity_id and _visibility.value > 1 and _varchar.attribute_id = 70 
				"; 
				$connection->query($sql);
				
				$sql = "update catalog_product_entity_varchar as _varchar 
							set _varchar.value = \"".$newUrlkey."\"	 
						where _varchar.entity_id = $entity_id  and _varchar.attribute_id = 115 
				"; 
				$connection->query($sql);
				
			
				
				//d($magentoProduct->getSku()); 
			 	// $id = $magentoProduct->getId();
			 	// $this->productModel->reset(); 
				 
				// $shippingGroup = $_data['Shipping Group'];
				
			 	//$magentoProduct = $this->productModel->load($id);
				//$magentoProduct->setMetaDescription($_data['METADESC']);
				//$magentoProduct->setMetaTitle($_data['METATITLE']); 
				//$magentoProduct->save();
				/*
				if ($magentoProduct->getVisibility()>1){
					if ($magentoProduct->getName()!=$_data['name']){
				   		$magentoProduct->setName($_data['name']);
				  		$magentoProduct->getResource()->saveAttribute($magentoProduct, 'name');
					}
				}   
				
				if ($magentoProduct->getUrlKey()!=$newUrlkey){
				  $magentoProduct->setUrlKey($newUrlkey); 
				  $magentoProduct->getResource()->saveAttribute($magentoProduct, 'url_key');
			 	} 
				 * 
				 */
				 
				dd($i." \ ".$total);
				
				  
			 // }else {
			 	// dd($_data['sku']." NOT FOUND");
			 // }
// 			  
	   }
		d('DONE');

    }
}