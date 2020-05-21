<?php
namespace ModernRetail\Import\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class Run  implements ObserverInterface{

    public $scopeConfig;
    public $import;
    public $session ;
    public $resourceConfig;
    public $helper;
    public $cacheListType;
    public $request;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Session $session,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $dataHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \ModernRetail\Import\Model\Log $log

    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->helper = $dataHelper;
        $this->import = $import;
        $this->resourceConfig = $resourceConfig;
        $this->cacheListType = $cacheTypeList;
        $this->request = $httpRequest;
        $this->log = $log;

    }

    public function execute(\Magento\Framework\Event\Observer $observer){

        /**
            Disable notices to avoid product save exceptions.
         */
        error_reporting(E_ALL ^ E_NOTICE);

        $bucket = $observer->getEvent()->getBucket();
        $file = $observer->getEvent()->getFile();

        $debug = false;
		  
		  if ($observer->getEvent()->getDebug()){
		  	$debug = true;
		  }
		  
		  $this->import->setPath($this->helper->getPath());
	      $this->import->setBucket($bucket);
	      $this->import->setXmlFile($file);
		  $this->import->setDebug($debug);
		  
	      @touch($this->helper->getPath().DS.$bucket.DS.$file.".lock");
	      @unlink($this->helper->getPath().DS.$bucket.DS.$file.".done");

        /**
         * Send info that job started
         */


        try {

            $return = $this->import->proccess();

            if ($return===true){

                /**
                 * send about job completion
                 */

                @unlink($this->helper->getPath().DS.$bucket.DS.$file.".lock");
                @touch($this->helper->getPath().DS.$bucket.DS.$file.".done");
                if(array_key_exists('mr_import', $_SESSION)) {

                    if ($_SESSION['mr_import']['log_file_id']) {
                        $file_id = $_SESSION['mr_import']['log_file_id'];
                        $this->log->log(
                            [
                                'file_id' => $file_id,
                                'status' => 'complete',
                                'message' => file_get_contents($this->import->getLogFile())
                            ]
                        );
                    }
                    if ($this->isDevMode()) {
                        $this->formJson(null, $bucket, $file);

                    }
                }

                die('DONE');
            }else {
                if(array_key_exists('mr_import', $_SESSION)) {

                    if($_SESSION['mr_import']['log_file_id']) {
                    $file_id = $_SESSION['mr_import']['log_file_id'];
                    $this->log->log(
                        [
                            'file_id' => $file_id,
                            'status' => 'failed',
                            'message' =>file_get_contents($this->import->getLogFile())
                        ]
                    );
                }
                }

                die("ERROR");
            }
        }catch(\Exception $ex){
            if(array_key_exists('mr_import', $_SESSION)) {
                if ($_SESSION['mr_import']['log_file_id']) {
                    $file_id = $_SESSION['mr_import']['log_file_id'];
                    $this->log->log(
                        [
                            'file_id' => $file_id,
                            'status' => 'error',
                            'message' => $ex->getMessage()
                        ]
                    );
                }
            }

            if($this->isDevMode()){
                $this->formJson($ex, $bucket , $file);
            }
            /**
             * send about job completion
             */
        }
        die("ERROR");
	}

	private function isDevMode(){
        if (stripos($this->request->getRequestUri(), '?dev=true') !== false) {
            return true;
        }

        return false;
    }

    private function formJson($ex = null, $bucket, $file)
    {

        if(!$ex){
            $response = [
                'status'=>'OK',
                'log'=>str_replace("\n\r",PHP_EOL, file_get_contents($this->import->getLogFile())),
                'file'=>str_replace("\n\r",PHP_EOL, file_get_contents($this->helper->getPath().DS.$bucket.DS.$file))
            ];
            die(json_encode($response));
        }


        $response = [
            'status'=>'ERROR',
            'exception'=>$ex->getMessage(),
            'log'=>str_replace("\n\r",PHP_EOL, file_get_contents($this->import->getLogFile())),
            'trace'=>$ex->getTraceAsString(),
            'file'=>str_replace("\n\r",PHP_EOL, file_get_contents($this->helper->getPath().DS.$bucket.DS.$file))
        ];
        die(json_encode($response));


    }

}