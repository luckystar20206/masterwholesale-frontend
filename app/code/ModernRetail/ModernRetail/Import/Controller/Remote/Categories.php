<?php


namespace ModernRetail\Import\Controller\Remote;


class Categories extends \Magento\Framework\App\Action\Action
{
   

	public function getPath(){
		return "/var/www/vhosts/kevinscatalog/magento/pub/media";
	}

    public function __construct(  \Magento\Framework\App\Action\Context $context,
     \ModernRetail\Import\Model\Xml $import, \ModernRetail\Import\Helper\Data $helper, 
     \Magento\Catalog\Model\Product $productModel,
	 \Magento\Catalog\Model\Category $categoryModel,
	  \Magento\Framework\App\ResourceConnection $resource
	 )
    {
        $this->import = $import;
        $this->helper = $helper;
		$this->productModel = $productModel;
		$this->resource = $resource;
		$this->categoryModel = $categoryModel; 
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



	public function _getCategories($string){
		$string = strtolower($string);
		$names = explode("/",$string);
		$readConnection = $this->resource->getConnection('core_read');
		$categories = array();
		foreach($names as $name){
			
			$whereParent = "";
			
			if (count($categories)>0){
				$parentCategoryId = $categories[count($categories)-1];
				$whereParent = " and cce.parent_id = ".$parentCategoryId." ";	
			}	
			$query = "select cce.entity_id, ccev.value from catalog_category_entity as cce LEFT JOIN catalog_category_entity_varchar as ccev on cce.entity_id = ccev.entity_id and attribute_id = 42 where LOWER(ccev.value) = '".$name."' $whereParent";
			
		
			
			$_cat = $readConnection->query($query)->fetchAll();
			if (count($_cat)>0){
				$categories[] = $_cat[0]['entity_id'];	
			} 
			
			
		}
		return $categories; 
		
	}  
	
	 
	public function _updateWeight($_product_id, $weight){
		 $writeConnection = $this->resource->getConnection('core_write');
		 $sql = "insert ignore into catalog_product_entity_decimal (attribute_id,store_id,entity_id,value) values (79,0,".$_product_id.",$weight) on duplicate key update value = $weight"; 
		 $writeConnection->query($sql);
	}
	
	
	public function _attachCategories($_product_id, $category_ids){
		 
		
		$writeConnection = $this->resource->getConnection('core_write');
		
		
				 
		foreach($category_ids as $_category_id){
				$sql = "insert ignore into catalog_category_product (category_id,product_id,position) values ($_category_id, $_product_id,0)";
				$writeConnection->query($sql);
			}
		
	}
	
	public function _enableProduct($product_id){
		 $writeConnection = $this->resource->getConnection('core_write');
		 $sql = "insert ignore into catalog_product_entity_int (attribute_id,store_id,entity_id,value) values (94,0,".$product_id.",1) on duplicate key update value = 1"; 
		 $writeConnection->query($sql);
	}
	
 
    public function execute()
    {
    	
	 	$readConnection = $this->resource->getConnection('core_read');
	  $data = $this->_readCsvFile($this->getPath()."/categories3.csv");
	  
	   foreach($data as $_data){
	  		
			//d($_data);
			 $sku = $_data['ITEM #']; 
			 $weight = floatval($_data['Weight']);
		
	  			
			  $this->productModel->reset(); 
			  $magentoProduct = $this->productModel->loadByAttribute("sku",$sku);
			  if (!$magentoProduct || !$magentoProduct->getId()) {
			  		dd($_data['ITEM #']." NOT FOUND");
			  		continue;
			  }
			
			$_product_id = $magentoProduct->getId(); 	
			
			$_data['ECOMMERCE CATEGORIES15'] = str_replace(", ",",",$_data['ECOMMERCE CATEGORIES15']);
			$_data['ECOMMERCE CATEGORIES15'] = str_replace(" ,",",",$_data['ECOMMERCE CATEGORIES15']);   
			
			$category_sets = explode(",",$_data['ECOMMERCE CATEGORIES15']); 
		   
			//if ($_product_id!=25015)  continue;
			//d($category_sets); 
		 
		 
		 	$writeConnection = $this->resource->getConnection('core_write');
		 	$sql = "delete from catalog_category_product where product_id = $_product_id";
			$writeConnection->query($sql); 
		 
		    foreach($category_sets as $_set){
		    
		    	$category_ids = $this->_getCategories(trim($_set));
				
				$this->_attachCategories($_product_id,$category_ids); 
				
				//$this->_enableProduct($_product_id);
				
				//$this->_updateWeight($_product_id,$weight);
				     
				if ($magentoProduct->getTypeId()=="configurable"){
					$children_ids = $readConnection->query("select product_id from catalog_product_super_link where parent_id = ".$_product_id)->fetchAll();
					if(count($children_ids)>0){
						foreach($children_ids as $_children_id){
							
							$writeConnection = $this->resource->getConnection('core_write');
						 	$sql = "delete from catalog_category_product where product_id = ".$_children_id['product_id'];
							$writeConnection->query($sql); 
							 
							$this->_attachCategories($_children_id['product_id'], $category_ids);
							//$this->_enableProduct($_children_id['product_id']);
							//$this->_updateWeight($_children_id['product_id'],$weight);
						}
					}
				}
				
		    }
		 
			//d($_product_id);  
		   dd($sku."[".$magentoProduct->getTypeId()."] saved");
	   }

    }
}
