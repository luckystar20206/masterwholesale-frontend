<?php 
namespace ModernRetail\Import\Plugin;
class ImportFlushCacheAvoidTagsResolver {
	
	

    private $mrhelper;
	
	
	public function __construct(
        \ModernRetail\Import\Helper\Data $mrhelper
    ) {

		$this->mrhelper = $mrhelper;
    }
	
	
	public function afterGetTags($a,$result)
    {
    	if (empty($result) && $this->mrhelper->isNeedToAutoClearCache()===false) {
    		return array(uniqid());
    	}
		if ($this->mrhelper->isNeedToAutoClearCache()===false){
			$catalogProductIdentities = array_search('catalog_product', $result);
			unset($result[$catalogProductIdentities]);
		}
		
		return $result;
    }
}
