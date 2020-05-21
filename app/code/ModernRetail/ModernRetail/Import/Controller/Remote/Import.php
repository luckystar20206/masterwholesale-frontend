<?php
namespace ModernRetail\Import\Controller\Remote;

class Import extends \ModernRetail\Import\Controller\RemoteAbstract
{
    const XML_CONFIG_API_LOGIN = "modernretail_import/credentials/login";
    const XML_CONFIG_API_PASSWORD = "modernretail_import/credentials/password";
    const XML_CONFIG_API_PROTECT_MODE = "modernretail_import/credentials/protect_mode";

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\Manager $eventManager,
        \ModernRetail\Import\Model\Log $log

    ){
        $this->log = $log;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->_eventManager = $eventManager;
        $this->helper = $helper;
        parent::__construct($context,$import,$helper,$resource,$storeManager);
    }

    private function _getLogin(){
        return $this->scopeConfig->getValue(self::XML_CONFIG_API_LOGIN,   \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    private  function _getPassword(){
        $password = $this->scopeConfig->getValue(self::XML_CONFIG_API_PASSWORD,   \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $password = $this->encryptor->decrypt($password);
        return $password;
    }

    private function _needCheckPermissions(){

        return (boolean)$this->scopeConfig->getValue(self::XML_CONFIG_API_PROTECT_MODE,   \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }


    public function execute()
    {
        try{
        ini_set('display_errors',1);
        $bucket = date("m-d-Y");

        /**
         * use Protect mode to deny WRITE requests
         */
        if ($this->_needCheckPermissions()){
            $httpRequestObject = new \Zend_Controller_Request_Http();
            $httpRequestObject->getHeader('APP_ID');
            $login = $httpRequestObject->getHeader('X-Login');
            $password = $httpRequestObject->getHeader('X-Password');

            if (@$_GET['X-Login']){
                $login = $_GET['X-Login'];
            }
            if (@$_GET['X-Password']){
                $password = $_GET['X-Password'];
            }
            /**
             * In case PHP AUTH
             */
            if (!$login && @$_SERVER['PHP_AUTH_USER']){
                $login = $_SERVER['PHP_AUTH_USER'];
            }
            if (!$password && @$_SERVER['PHP_AUTH_PW']){
                $password = $_SERVER['PHP_AUTH_PW'];
            }

            /**
             * Deny if exist
             */

            if ($login!=$this->_getLogin() || $password!=$this->_getPassword()){
                header('HTTP/1.0 403 Forbidden');
                echo "You are not authorized to call integrator requests. Please supply valid API credentials within your request ";
                exit;
            }
        }

        if(isset($_FILES['mr_import_file']['name']) && $_FILES['mr_import_file']['name'] != '') {
            $localFile = str_replace(" ", "_", $_FILES['mr_import_file']['name']);
            /* Starting upload */
            $file_id = uniqid();


            $uploader = new \Magento\Framework\File\Uploader('mr_import_file');

            // Any extention would work
            $uploader->setAllowedExtensions(array('xml'));
            $uploader->setAllowRenameFiles(false);

            // Set the file upload mode
            // false -> get the file directly in the specified folder
            // true -> get the file in the product like folders
            //    (file.jpg will go in something like /media/f/i/file.jpg)
            $uploader->setFilesDispersion(false);

            // We set media as the upload dir
            @mkdir($this->helper->getPath() . DS . $bucket);
            $path = $this->helper->getPath() . DS . $bucket;

            $uploader->save($path, $localFile);

            @unlink($this->helper->getPath() . DS . $bucket . DS . $localFile . ".done");

            $type = $this->helper->getIntegrationFileType($_FILES['mr_import_file']['name']);

            $this->log->log(
                [
                    'file_id' => $file_id,
                    'file_name' => $_FILES['mr_import_file']['name'],
                    'status' => 'processing',
                    'type' => $type,
                    'message' =>'Started File Upload',
                    'path_to_file'=>$bucket . DS . $localFile
                ]
            );

            $_SESSION['mr_import']['log_file_id'] = $file_id;

            /**
             * Simulate job started from ROY
             */

            /**
             * Notify we received job
             */

            /**
             * Temporary send started code
             */



            $this->eventManager->dispatch("integrator_run_file", array("bucket" => $bucket, "file" => $localFile, "debug" => false, 'reindex' => false));

            die("EXECUTED");
        }

        }catch(\Exception $e){
            $this->log->log(
                    [
                        'file_id' => $file_id,
                        'file_name' => $_FILES['mr_import_file']['name'],
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ]
            );
            }
    }
}