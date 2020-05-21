<?php


namespace ModernRetail\Import\Controller\Remote;


 
class Kcategories extends \Magento\Framework\App\Action\Action
{
   

	public function getPath(){
		return "/var/www/vhosts/kevinscatalog/magento/pub/mr_import";
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
			
			//ci($connection);
			
			$categoryModel = 			$resource = $objectManager->get('Magento\Catalog\Model\Category');
			$collection = $categoryModel->getCollection()->addAttributeToSelect("name");
			
			
			$catNames = array();
			foreach($collection as $_cat){
				$catNames[$_cat->getId()] = $_cat->getName();
			}  
			
			file_put_contents($this->getPath()."/product-categories2.csv", "");
			$fp = fopen($this->getPath()."/product-categories2.csv", 'w');

			 
    	
		$result = $connection->query("select cpe.entity_id, cpe.sku, _at_name.value as name, group_concat(ccp.category_id) as categories, group_concat(cce.path) as categories_paths   from catalog_product_entity  as cpe
			left join catalog_product_entity_varchar as _at_name on  cpe.entity_id = _at_name.entity_id and attribute_id = (select attribute_id from eav_attribute where attribute_code='name' and entity_type_id = 4)
            
			left join catalog_category_product as ccp on cpe.entity_id = ccp.product_id
            left join catalog_category_entity as cce on ccp.category_id = cce.entity_id
            group by entity_id");
			
			$result = $result->fetchAll();
			$i = 0;
			foreach($result as $_res){ 
				$_category_pathes = explode(",",$_res['categories_paths']);
				$categoryPathes = [];
				foreach($_category_pathes as $_path){
					$__path = [];
					$_path = str_replace("1/2/", "", $_path);
					$_path = explode("/",$_path);
					foreach($_path as $_category_id){
						$category = $collection->getItemById($_category_id);
						if ($category)
							$__path[] = $category->getName();	
					}
					$categoryPathes[] = join("/",$__path);
				}
				
				
				$_toCsv = [
					"product_id"=>$_res['entity_id'],
					"name"=>$_res['name'],
					"sku"=>$_res['sku'],
					"categories"=>'"'.join(";",$categoryPathes).'"', 
					"category_ids"=>$_res['categories']
				];
				
			
				if ($i==0){
					fputcsv($fp,array_keys($_toCsv));
				}
				
				fputcsv($fp, $_toCsv);
				$i++;
			}

		 
		
		
    	 
		fclose($fp);
		 
     
		die('DOdNE');  
	
    }
    
    
        public function execute2()
    {
				
		
		
		
			
				
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();    	
			
			//ci($connection);
			
			$categoryModel = 			$resource = $objectManager->get('Magento\Catalog\Model\Category');
			$collection = $categoryModel->getCollection()->addAttributeToSelect("name");
			
			
			$catNames = array();
			foreach($collection as $_cat){
				$catNames[$_cat->getId()] = $_cat->getName();
			}
			
			file_put_contents($this->getPath()."/category-products2.csv", "");
			$fp = fopen($this->getPath()."/category-products2.csv", 'w');
 
			
    	 
		$result = $connection->query('select ccp.category_id, cce.path , group_concat(concat_ws(" ",cpe.sku)) as SKUS, group_concat(concat_ws(" ",cpe.entity_id)) as PRODUCT_IDS  from catalog_category_product as ccp  
			left join catalog_product_entity as cpe on ccp.product_id = cpe.entity_id 
			left join catalog_category_entity as cce on ccp.category_id = cce.entity_id group by ccp.category_id');
			
			$result = $result->fetchAll();
			$i = 0;
			
			foreach($result as $_res){
				// d($_res); 
				// $_category_pathes = explode(",",$_res['categories_paths']);
				 $categoryPathes = [];
				// foreach($_category_pathes as $_path){
					$__path = [];
					 
					$_path = explode("/",str_replace("1/2/","",$_res['path']));
					foreach($_path as $_category_id){
						$category = $collection->getItemById($_category_id);
						if ($category)
							$__path[] 	= $category->getName();	
					}
					$categoryPathes[] = join("/",$__path);
				//}
				
				$_res['categories'] = join(";",$categoryPathes); 
				$_toCsv = ["category_id"=>$_res['category_id'],'category'=>$_res['categories'],'skus'=>$_res['SKUS'],'product_ids'=>$_res['PRODUCT_IDS']];
				
				if ($i==0){
					fputcsv($fp,array_keys($_toCsv));
				} 
			
 				fputcsv($fp, $_toCsv); 
				$i++;
			}

		
		
		
    	
		fclose($fp); 
		
     
		die('DONdd E');  
	
    }
    
}