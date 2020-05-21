<?php
namespace ModernRetail\Import\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
class Monitor  extends AbstractHelper{

    const XML_LAST_SENT_TIME = "modernretail_import/monitor/information_sent_at";
    const XML_SENT_INTERVAL_SECONDS = 15*60;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \ModernRetail\Import\Helper\Monitor\Api $monitorApi,
        \ModernRetail\Import\Helper\Version $versionHelper,
        \Magento\Framework\App\ResourceConnection $resource

    )
    {
        $this->context = $context;
        $this->resourceConfig = $resourceConfig;
        $this->monitorApi = $monitorApi;
        $this->versionHelper = $versionHelper;
        $this->resource = $resource;

    }


    private function _getModules(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleList = $objectManager->get('Magento\Framework\Module\ModuleList');
        $_return = [];
        foreach ($moduleList->getAll() as $module){
            if (strpos($module['name'],"Magento_")!==false) continue;
            $_return[] = join(" ",[$module['name'],$module['setup_version']]);
        }
       return $_return;


    }


    public function sendSystemInformation(){


        $core_config_data = $this->resource->getTableName("core_config_data");

        $sql = "select value from $core_config_data where path ='".self::XML_LAST_SENT_TIME."'";
        $result = $this->resource->getConnection('core_read')->query($sql)->fetchObject();
        $needToSend = false;
        if ($result) {
           $last = $result->value;
            if (time()-$last > self::XML_SENT_INTERVAL_SECONDS){
                $needToSend = true;
            }
        }else {
            $needToSend = true;
        }

          if ($needToSend===false) return $this;


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion(); //will return the magento version


        $serverInformation = [
            "hostname"=>gethostname(),
            "ip"=> gethostbyname(gethostname()),
            "webserver"=>$_SERVER['SERVER_SOFTWARE']
        ];

        $phpInformation = [
            "version"=>phpversion(),
            "memory_limit"=>ini_get('memory_limit'),
            "max_execution_time"=>ini_get('max_execution_time'),
        ];

        $magentoInformation = [
                "version"=>$productMetadata->getVersion(),
                "modules"=>$this->_getModules()
        ];

        $information = [
            "integrator"=>[
               "code"=>"magento2",
               "version"=> $this->versionHelper->getCurrentVersion(),
            ],
            "server"=>$serverInformation,
            "php"=>$phpInformation,
            "magento"=>$magentoInformation,

        ];


        try {

            $this->monitorApi->apiPOST("/information", $information);

        }catch (\Exception $ex){
            /**
             * is we really need to update
             */
        }

        $this->resourceConfig->saveConfig(
            "modernretail_import/monitor/information_sent_at",
            time(),'default', 0
        );
    }

    
    public function sendJob($job){
        if (array_key_exists('type',$job )===false){
            $parts = explode("_",$job['job_id']);
            $job['type'] = array_shift($parts);
        }

        if (in_array($job['type'],['np','inv','sprc','attr','unknown'] )===false){
            $job['type'] = 'unknown';
        }
        $job_id = str_replace(".xml", "",$job['job_id']);
        $job['job_id'] = $job_id;

        try {
          
            $this->monitorApi->apiPOST("/monitor/job", $job);
        }catch (\Exception $ex){

            /**
             * is we really need to update
             */
        }
    }

}
