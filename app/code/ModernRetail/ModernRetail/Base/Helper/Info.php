<?php

namespace ModernRetail\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Info extends AbstractHelper
{

    private $_version;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $magentoVersion,
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ModernRetail\Base\Helper\Api $apiHelper
    )
    {
        $this->context = $context;
        $this->fullModuleList = $fullModuleList;
        $this->magentoVersion = $magentoVersion;
        $this->apiHelper = $apiHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }


    public function getIntegratorVersion(){
        if ($this->_version) return $this->_version;
        $this->_version =  file_get_contents(dirname(__FILE__).'/../../integrator.version');
        return $this->_version;
    }


    public function getMagentoVersion(){
        return $this->magentoVersion->getVersion();
    }


    public function getSystemInformation($store_id = 0){

        $magento = [
            'version'=>$this->getMagentoVersion(),
        ];

        $php = [
            'version'=>phpversion()
        ];

        $integrator = [
            'modules'=>[],
            'configuration'=>[]
        ];

        $integrator['configuration'] = [];

        foreach(['modernretail_base','modernretail_import'] as $section){
            $integrator['configuration'][$section] = $this->context->getScopeConfig()->getValue($section, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
        }

        $allModules = $this->fullModuleList->getAll();
        foreach($allModules as $module=>$data){
            if (strpos($module,'ModernRetail')!==false)
                $integrator['modules'][$module] = $data['setup_version'];
        }

        $data = [
            'platform'=>"magento2",
            'base_url'=>$this->storeManager->getStore()->getBaseUrl(),
            'magento_version'=>$this->getMagentoVersion(),
            'integrator_version'=>$this->getIntegratorVersion(),
            'magento'=>$magento,
            'php'=>$php,
            'integrator'=>$integrator
        ];

        return $data;
    }

    public function sendSystemInformation(){
        $stores = $this->storeManager->getStores();
        foreach($stores as $store){
            $data = $this->getSystemInformation($store->getId());
            $this->apiHelper->apiPOST('monitor/information',$data);
        }
    }
}