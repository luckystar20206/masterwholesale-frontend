<?php


namespace ModernRetail\Import\Controller\Remote;


 
class Ktemp extends \Magento\Framework\App\Action\Action
{
   

	

    public function __construct(  \Magento\Framework\App\Action\Context $context, \ModernRetail\Import\Model\Xml $import, \ModernRetail\Import\Helper\Data $helper, \Magento\Catalog\Model\Product $productModel,\Magento\Catalog\Model\Product\Url $urlModel,\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->import = $import;
        $this->helper = $helper;
		$this->urlModel = $urlModel;
		$this->productModel = $productModel;
		 $this->_resource = $resource;
		
        parent::__construct($context);
    }


	private function multiExplode($delimiters,$string) {
	    return explode($delimiters[0],strtr($string,array_combine(array_slice($delimiters,1),array_fill(0,count($delimiters)-1,array_shift($delimiters)))));
	}
	
	public function  array_isearch($str, $array){
		  $found = array();
		  foreach ($array as $k => $v) {
		    if (strtolower($v) == strtolower($str)) {
		      $found[] = $k;
		    }
		  }
		  return $found;
		}

 
    public function execute()
    {
    	
		
		
		
		
		
		
		$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
		$data = $connection->fetchAll('select entity_id, sku from catalog_product_entity');
		
		
		$hashes = [];
		
		$target = [];
		
		foreach($data as $_data){ 
			$sku = strtolower($_data['sku']);
			$sku = $this->multiExplode(["|","-"], $sku);
			sort($sku);
			$hashes[join("@",$sku)][] = $_data['sku'];
			$target[$_data['sku']] = join("@",$sku);    
		}
		
		$result = [];
		foreach($target as $sku=>$hash){
			
			$return = $hashes[$hash];
			
			if (count($return)>1){ 
				//d($sku." has dublicates");
				sort($return);
				$result[] = join(",",$return);
			} 
		}
		$target = array_unique($result);  
		d($result); 
			
		d(__LINE__);

    }
}