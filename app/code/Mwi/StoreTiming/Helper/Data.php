<?php
/**
 * Copyright © 2015 Mwi . All rights reserved.
 */
namespace Mwi\StoreTiming\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Mwi\StoreTiming\Model\ResourceModel\Timing\CollectionFactory $storeTiming,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		parent::__construct($context);
		$this->_storeTiming =	$storeTiming;
		$this->_scopeConfig = $scopeConfig;
	}
	/*
	public function getTodayTiming(){
		$now 	= new \DateTime();
		$timings = $this->_storeTiming->create()
									->addFieldToFilter('date', ['lteq' => $now->format('Y-m-d 23:59:59')])
				        	->addFieldToFilter('date', ['gteq' => $now->format('Y-m-d 00:00:00')]);
		if(!empty($timings) && $timings->getSize() > 0){
			$storeTime =	$timings->getFirstItem()->getMessage();
		} else {
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
			$storeTime 	=	$this->scopeConfig->getValue("todayhoursdefault/defaulttime/defaulttimetext", $storeScope);
		}
		return $storeTime;
	}
	*/
	public function getTodayTiming() {

		$now = new \DateTime(null, new \DateTimeZone('America/Los_Angeles'));
		$timings = $this->_storeTiming->create()
									->addFieldToFilter('date', ['lteq' => $now->format('Y-m-d 23:59:59')])
									->addFieldToFilter('date', ['gteq' => $now->format('Y-m-d 00:00:00')]);
		if(!empty($timings) && $timings->getSize() > 0){
			$storeTime = $timings->getFirstItem()->getMessage();
		} else {
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
			if(date('D',time()) == 'Sat'){
					$storeTime = $this->scopeConfig->getValue("todayhoursdefault/defaultsaturday/defaulttimetext", $storeScope);
			} elseif(date('D',time()) == 'Sun'){
					$storeTime = $this->scopeConfig->getValue("todayhoursdefault/defaultsunday/defaulttimetext", $storeScope);
			} else {
					$storeTime = $this->scopeConfig->getValue("todayhoursdefault/defaulttime/defaulttimetext", $storeScope);
			}
		}
		return $storeTime;
	}
}
