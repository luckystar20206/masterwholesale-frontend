<?php
namespace ModernRetail\Import\Controller\Remote;

class Upgrade extends \Magento\Framework\App\Action\Action
{
	const HOT_POINT = "https://admin.modernretail.com/";
	
	public function __construct(  \Magento\Framework\App\Action\Context $context, \ModernRetail\Import\Helper\Version $versionHelper,\Magento\Framework\Filesystem\DirectoryList $dir)
    {
      	$this->versionHelper = $versionHelper;
		$this->dir = $dir;
		parent::__construct($context);
    }
	
	public function _getFromHotPoint($path){
		
		$ch = curl_init();
 
		$timeout = 60;
		$user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
	    curl_setopt ($ch, CURLOPT_URL, self::HOT_POINT.$path);
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
	
	public function updateFile($file,$content){




		//d($this->dir->getRoot());
		$root  = $this->dir->getRoot();
		$targetFile = $root."/".$file;
		$targetFile = str_replace("//","/",$targetFile);
		
		if(file_exists($targetFile)){
			if (is_writable($targetFile)===false) throw new \Exception($file." is not writable");
			return file_put_contents($targetFile, $content);
		}else {
			/**
			 * File doesnot exist.. we need to create it
			 */
			 $path = explode('/',$file);
			 $fileName = array_pop($path);
			 
			 if (file_exists($root."/".join("/",$path))===false){
			 	mkdir($root."/".join("/",$path),0777,true); 
			 }
			 if (is_writable($root."/".join("/",$path))===false){ 
			 	 throw new \Exception($root."/".join("/",$path)." is not writable");
			 }
			 return file_put_contents($targetFile, $content); 
		}
			
		return false; 
		
	} 
	 
    public function execute()
    {
    	if (array_key_exists('force', $_GET)===false){		 
			if ($this->versionHelper->isNeedUpgrade()===false){
				die(json_encode(array('error'=>true,'message'=>'Latest version is installed')));
			}  
		}
	 
		$response = $this->_getFromHotPoint('integratorapi/files/?magento_version=2');
		$response = json_decode($response,true);
		$files = $response['files'];
		$repoVersion = $response['repo_version'];
		$errorTrigger = false;
		
		foreach($files as $file){

		   if ($file[0]=="/") $file = substr($file, 1);

            if (strpos($file,"onfig.xml")>0) continue;

			try {
				$this->updateFile($file,$this->_getFromHotPoint("integratorapi/file?repo_version=$repoVersion&file=$file"));
				$updatedFiles[$file] = array("error"=>false); 
			}catch(\Exception $ex){
				$updatedFiles[$file] = array("error"=>true,"message"=>$ex->getMessage());
				$errorTrigger = true;
			}
		}
		if ($errorTrigger===true){
			die(json_encode(array('error'=>true,'message'=>'Some files was not updated properly. Please check','files'=>$updatedFiles)));
		}
		die(json_encode(array('error'=>false,'message'=>'Wiles was updated','files'=>$updatedFiles)));
    }
	
}