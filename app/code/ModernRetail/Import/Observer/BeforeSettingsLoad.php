<?php
namespace ModernRetail\Import\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class BeforeSettingsLoad  implements ObserverInterface{

    public $scopeConfig;
    public $import;
    public $session ;
    public $resourceConfig;
    public $helper;
    public $cacheListType;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Session $session,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $dataHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList

    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->helper = $dataHelper;
        $this->import = $import;
        $this->resourceConfig = $resourceConfig;
        $this->cacheListType = $cacheTypeList;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){


        $session = $this->session;
        $import = $this->import;

        $import->setPath($this->helper->getPath());


        /*
         * Flag to reload the page
         */
        $needToReload = false;


        /// =============== USE CATEGORIES SETTINGS ========================
        $useCategoriesOld = $useCategories = $this->scopeConfig->getValue("modernretail_import/settings/use_categories");
       

        $useCategoriesXML = $import->xconfig("use_categories");
        $useCategoriesXML = array_shift($useCategoriesXML)."";
        //d($useCategoriesXML);

        if ($useCategoriesOld != $useCategoriesXML){
          //  Mage::getModel('core/config')->saveConfig("modernretail_import/settings/use_categories", $useCategoriesXML,'default', 0);
            $this->resourceConfig->saveConfig(
                "modernretail_import/settings/use_categories",
                $useCategoriesXML,'default', 0
            );

            $needToReload = true;
        }




        /// =============== NEW PRODUCT STATUS ========================
        $newProductStatusOld = $useCategories = $this->scopeConfig->getValue("modernretail_import/settings/new_products_status");

        $newProductStatuxXML = $import->xconfig("new_products_status");
        $newProductStatuxXML = array_shift($newProductStatuxXML)."";


        if ($newProductStatusOld != $newProductStatuxXML){

            //Mage::getModel('core/config')->saveConfig("modernretail_import/settings/new_products_status",$newProductStatuxXML,'default', 0);

            $this->resourceConfig->saveConfig(
                "modernretail_import/settings/new_products_status",
                $newProductStatuxXML,'default', 0
            );

            $needToReload = true;
        }




        /////===============CONFIGURABLE MAPPPING========================
        $configurableMapping = $this->scopeConfig->getValue("modernretail_import/configurable_mapping/json_value");
        $configurableMappingMD5 = md5($configurableMapping);
        $configurableMapping = json_decode($configurableMapping,true);

        /**
         * DISABLE ALL FIELDS BEFORE READING FROM XML
         */
        if ($configurableMapping){
            foreach($configurableMapping as $k=>$v){
                $configurableMapping[$k]['status'] = false;
            }
        }else {
            $configurableMapping = array();
        }

        $configurableMappingXML = $import->xconfig("configurable_product_attributes/attribute");
        foreach($configurableMappingXML as $xmlAttribute){
            $isConfigurable = $import->attr($xmlAttribute,"configurable");
            if ($isConfigurable=="true" || $isConfigurable=="1"){
                $isConfigurable =  true;
            }else {
                $isConfigurable = false;
            }

            $configurableMapping[$import->attr($xmlAttribute,"tag")]
                = array(
                "attribute"=>$import->attr($xmlAttribute,"attribute_code"),
                "status"=>true,
                "is_configurable"=>$isConfigurable
            );

        }



        if ($configurableMappingMD5!==md5(json_encode($configurableMapping))){
            $this->resourceConfig->saveConfig(
                "modernretail_import/configurable_mapping/json_value",
                json_encode($configurableMapping),'default', 0
            );
            //Mage::getModel('core/config')->saveConfig("modernretail_import/configurable_mapping/json_value", json_encode($configurableMapping),'default', 0);
            $needToReload = true;
        }







        ///===========SIMPLE MAPPING==================

        $simpleMapping = $this->scopeConfig->getValue("modernretail_import/simple_mapping/json_value");

        $simpleMappingMD5 = md5($simpleMapping);
        $simpleMapping = json_decode($simpleMapping,true);

        /**
         * DISABLE ALL FIELDS BEFORE READING FROM XML
         */
        if ($simpleMapping){
            foreach($simpleMapping as $k=>$v){
                $simpleMapping[$k]['status'] = false;
            }
        }else {
            $simpleMapping = array();
        }

        $simpleMappingXML = $import->xconfig("simple_product_attributes/attribute");
        foreach($simpleMappingXML as $xmlAttribute){
            $isConfigurable = $import->attr($xmlAttribute,"configurable");
            if ($isConfigurable=="true" || $isConfigurable=="1"){
                $isConfigurable =  true;
            }else {
                $isConfigurable = false;
            }

            $simpleMapping[$import->attr($xmlAttribute,"tag")]
                = array(
                "attribute"=>$import->attr($xmlAttribute,"attribute_code"),
                "status"=>true,
                "is_configurable"=>$isConfigurable
            );

        }

        if ($simpleMappingMD5!==md5(json_encode($simpleMapping))){

            $this->resourceConfig->saveConfig(
                "modernretail_import/simple_mapping/json_value",
                json_encode($simpleMapping),'default', 0
            );

            $needToReload = true;
        }



        ///===========UPDATE PRODUCTS MAPPING==================


        $updateMapping = $this->scopeConfig->getValue("modernretail_import/update_mapping/json_value");
       
        $updateMappingMD5 = md5($updateMapping);
        $updateMapping = json_decode($updateMapping,true);


        if ($updateMapping){
            foreach($updateMapping as $k=>$v){
                $updateMapping[$k]['status'] = false;
            }
        }else {
            $updateMapping = array();
        }

        $updateMappingXML = $import->xconfig("update");
        $updateMappingXML = $updateMappingXML[0];
        foreach($updateMappingXML->children() as $k=>$v){
            $updateMapping[$k] = array(
                "attribute"=>strval($v),
                "status"=>true,
                "is_configurable"=>false
            );
        }

        if ($updateMappingMD5!==md5(json_encode($updateMapping))){

           
            $this->resourceConfig->saveConfig(
                "modernretail_import/update_mapping/json_value",
                json_encode($updateMapping),'default', 0
            );


            $needToReload = true;
        }


        if ($needToReload){
           // $this->session->addSuccess('MR Integrator settings updated from config.xml');
            //Mage::app()->getCacheInstance()->cleanType('config');
            $this->cacheListType->cleanType('config');
          //  die("<script type='text/javascript'>document.location.reload()</script>");
        }



    }

}