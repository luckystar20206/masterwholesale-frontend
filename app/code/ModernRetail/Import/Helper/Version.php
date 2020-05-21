<?php
namespace ModernRetail\Import\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
class Version  extends AbstractHelper{
	
	private $_version = null;
	private $_latestVersion = null;
	
	const VERSION_URL = "https://admin.modernretail.com/integratorapi/versions";
	
	
	private function _getFromUrl($url){
		$ch = curl_init();
 
		$timeout = 15;
		$user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
	    curl_setopt ($ch, CURLOPT_URL, $url);
	    curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
	    curl_setopt ($ch, CURLOPT_HEADER, 0);
	    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt ($ch,CURLOPT_CONNECTTIMEOUT,120);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt ($ch,CURLOPT_TIMEOUT,120);
	    curl_setopt ($ch,CURLOPT_MAXREDIRS,10);
	    //curl_setopt ($ch,CURLOPT_COOKIEFILE,"cookie.txt");
	    //curl_setopt ($ch,CURLOPT_COOKIEJAR,"cookie.txt");
	      
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	public function getCurrentVersion(){
		if ($this->_version) return $this->_version;
		$this->_version =  file_get_contents(dirname(__FILE__).'/../../integrator.version');
		return $this->_version;
	}
	
	public function getLatestVersion(){

		if ($this->_latestVersion) return $this->_latestVersion;

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$monitorApi = $objectManager->get('\ModernRetail\Base\Helper\Api');
		$info = $monitorApi->apiGET("monitor/version/magento2");

		if ($info){
			$this->_latestVersion = $info['version'];
			return  $info["version"];
		}

		if ($this->_latestVersion) return $this->_latestVersion;

		$json = $this->_getFromUrl(self::VERSION_URL);
		$json = json_decode($json,true);
		if (is_array($json)===false){
			return false;
		}

		$this->_latestVersion = $json['magento_2'];
		return $this->_latestVersion;

	}


	public function getLatestVersionFromGIT(){


		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();


		if ($this->_latestVersion) return $this->_latestVersion;

		$json = $this->_getFromUrl(self::VERSION_URL);
		$json = json_decode($json,true);
		if (is_array($json)===false){
			return false;
		}

		$this->_latestVersion = $json['magento_2'];
		return $this->_latestVersion;

	}
	
	
	public function isNeedUpgrade(){
		if (version_compare($this->getCurrentVersion(), $this->getLatestVersion())!=0) return true;
		return false;
	}
	
}