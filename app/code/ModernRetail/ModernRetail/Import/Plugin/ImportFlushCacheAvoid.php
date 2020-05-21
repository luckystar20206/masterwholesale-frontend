<?php 
namespace ModernRetail\Import\Plugin;
class ImportFlushCacheAvoid {
	
    private $identifierStrategy;
    private $mrhelper;
	
	
	public function __construct(
        \Magento\Framework\App\Cache\Tag\Strategy\Identifier $identifierStrategy,
        \ModernRetail\Import\Helper\Data $mrhelper
    ) {
        $this->identifierStrategy = $identifierStrategy;
		$this->mrhelper = $mrhelper;
    }
 
	
	public function afterGetStrategy($a,$result)
    {
        if (get_class($result)=='Magento\Framework\App\Cache\Tag\Strategy\Dummy' && $this->mrhelper->isNeedToAutoClearCache()===false){
        	return $this->identifierStrategy;
        }
		return $this->identifierStrategy; 
    }
}
