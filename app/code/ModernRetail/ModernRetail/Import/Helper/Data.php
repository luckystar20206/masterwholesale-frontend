<?php
namespace ModernRetail\Import\Helper;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;

class Data  extends AbstractHelper{

    const XML_CONFIG_CLEANUP_DAYS = "modernretail_import/settings/cleanup_days";
    const XML_CONFIG_USE_SYSTEM_CALLS = "modernretail_import/settings/can_use_system_calls";
    const XML_CONFIG_FLUSH_CACHE = "modernretail_import/settings/flush_cache";


    private $_storeMapping = array();
    private $_flatDataEnabled = true;
    public $scopeConfig;

    public $dir;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Eav\Model\Entity\Attribute $attribute_model,
        \Magento\Eav\Model\Entity\Attribute\Source\Table $attribute_source_table,

        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSetCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\ProductMetadataInterface $magentoVersion,
        \Magento\Framework\Registry $registry
    )
    {
        $this->dir = $dir;
        $this->storeManager = $storeManager;
        $this->attribute_model = $attribute_model;
        $this->attribute_source_model = $attribute_source_table;
        $this->scopeConfig = $context->getScopeConfig();
        $this->resourceProduct = $resourceProduct;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->resource = $resource;
        $this->magentoVersion = $magentoVersion;
        $this->registry = $registry;

        parent::__construct($context);
    }


    public function getMagentoVersion(){
        return $this->magentoVersion->getVersion();
    }


    public function getAttributeValue($arg_attribute, $arg_value) {

        if (is_string($arg_value) &&  strcmp($arg_value, "0")==0){

        }else if (is_null($arg_value) || $arg_value=="") return null;

        $attribute_model        = $this->attribute_model;
        $attribute_options_model= $this->attribute_source_model;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $attribute_options_model = $objectManager->create(get_class($this->attribute_source_model));

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);



        if ($attribute->usesSource()===false || ($attribute->getSourceModel()!="Magento\Eav\Model\Entity\Attribute\Source\Table"
                && $attribute->getSourceModel()!="")
        ){
            return $arg_value;
        }

        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);



        foreach($options as $option){
            $_option_id = $attribute_model->setStoreId(0)->getSource()->getOptionId($arg_value);
            if ($_option_id) return $_option_id;
            /*
            if ($option['label'] == $arg_value){
                return $option['value'];
            }
            */
        }


        $arr = array($arg_value,$arg_value);

        $value['option'] = $arr;
        $result = array('value' => $value);


        $attribute->setData('option',$result);
        $attribute->save();

        $eav_attribute_option_value = $this->resource->getTableName('eav_attribute_option_value');
        $arg_value = addslashes($arg_value);
        $sql = "SELECT option_id FROM `$eav_attribute_option_value` WHERE `value` LIKE \"$arg_value\"";
        $readConnection = $this->resource->getConnection('core_read');
        $result = $readConnection->query($sql)->fetchObject();
        if ($result){
            return $result->option_id;
        } else {
            return false;
        }

    }

    public function getPath(){
        return $this->dir->getRoot()."/pub/mr_import/data";
    }


    public function getBuckets(){

        $array = array();
        if ($handle = opendir( $this->getPath().DS)) {
            $i=0;
            while (false !== ($entry = readdir($handle))) {
                $i++;
                if ($entry != "." && $entry != ".." && is_dir($this->getPath().DS.$entry)) {

                    $array[$entry] = $entry;
                }
            }
            closedir($handle);
        }

        $newArr = [];
        foreach($array as $k=>$v){
            try {
                list($m, $d, $y) = @explode("-", $k);
                $newK = strtotime ("$y-$m-$d");
            }catch (\Exception $ex){
                $newK = $k;
            }



            $newArr[$newK] = $v;
        }

        ksort($newArr);
        $newArr = array_reverse($newArr);
        $newArr = array_combine($newArr,$newArr );

        return $newArr;
    }


    public function getFiles($bucket){
        $array = array();
        if ($handle = opendir( $this->getPath().DS.$bucket.DS)) {
            $i=0;
            while (false !== ($entry = readdir($handle))) {
                $i++;
                if ($entry != "." && $entry != ".." && is_file($this->getPath().DS.$bucket.DS.$entry)) {
                    if (strpos($entry, ".log")) continue;
                    if (strpos($entry, ".done")) continue;
                    if (strpos($entry, ".lock")) continue;

                    if (file_exists($this->getPath().DS.$bucket.DS.$entry.".done")){
                        $array[$entry] = $entry." - Already imported";
                    }else {
                        $array[$entry] = $entry;
                    }
                }
            }
            closedir($handle);
        }

        asort($array);

        return $array;
    }



    public function getExcludedAttributes(){
        $attributes = $this->scopeConfig->getValue("modernretail_import/settings/attributes");
        return explode(",",$attributes);
    }

    /**
     * remove excluded attributes during update product
     */
    public function removeExcludedAttributes($data){
        $attributes = $this->getExcludedAttributes();

        foreach ($data as $attribute_code=>$value) {
            if (in_array($attribute_code, $attributes)){
                unset($data[$attribute_code]);
            }
        }
        return $data;
    }


    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }


    public function needSkipExist(){

        if ($this->scopeConfig->getValue("modernretail_import/settings/skip_exist")==1) return true;

        return false;
    }


    public function needSkipConfigurableIfExist(){

        if ($this->scopeConfig->getValue("modernretail_import/settings/skip_exist_configurable")==1) return true;

        return false;
    }



    public function getSimpleProductType(){
        if($this->scopeConfig->getValue("modernretail_import/settings/use_virtual_products")==1) return 'virtual';
        return 'simple';
    }


    public function needSkipSimpleIfExist(){

        if ($this->scopeConfig->getValue("modernretail_import/settings/simple_skip_exist")==1) return true;

        return false;
    }

    public function getMinQty(){
        return intval($this->scopeConfig->getValue("cataloginventory/item_options/min_qty"));
    }

    public function removeOldFiles($days = null){

        //d($this->canUseSystemExec());
        if (!$days){
            $days = $this->scopeConfig->getValue(self::XML_CONFIG_CLEANUP_DAYS);
        }


        $buckets = $this->getBuckets();
        foreach($buckets as $bucket){

            //d(time()-filectime($this->getPath().DS.$bucket));

            list($m,$d,$y) = explode("-",$bucket);
            try {
                $createTime = new DateTime("$y-$m-$d");
                $nowTime = new DateTime(now());
                $diff = $createTime->diff($nowTime);

                if ((time()-filectime($this->getPath().DS.$bucket) > $days*3600*24) || ($diff->format("%R")=="+" && $diff->format("%a")>$days) ){
                    if ($this->canUseSystemExec()===false){
                        $this->rrmdir($this->getPath().DS.$bucket);
                    }else {
                        system("rm -rf \"".$this->getPath().DS.$bucket."\" ");
                    }
                }
            }catch(Exception $ex){
                //we willnt remove buckets which have not date in name
            }
        }
    }



    public function getWebsiteByStoreCode($storeCode){
        $return =  $this->_getByStoreCode($storeCode);
        if ($return){
            return $return['website'];
        }
    }


    public function getStoreByStoreCode($storeCode){
        $return =  $this->_getByStoreCode($storeCode);
        if ($return){
            return $return['store'];
        }
    }

    private function _getByStoreCode($storeCode){

        if (!$storeCode) {
            $storeCode = "default";
        }
        if (array_key_exists($storeCode, $this->_storeMapping)) {
            return $this->_storeMapping[$storeCode];
        }
        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    //$store is a store object
                    if ($store->getCode()==$storeCode){
                        $this->_storeMapping[$storeCode] = array(
                            "store"=>$store,
                            "website"=>$website
                        );
                        return $this->_storeMapping[$storeCode];
                    }
                }
            }
        }
    }


    public function getAttributeSetId($data){

        $entityTypeId           = $this->resourceProduct->getTypeId();
        $attributeSetCollection = $this->attributeSetCollection;
        $attributeSetCollection  ->setEntityTypeFilter($entityTypeId);

        $valueFromConfig = $this->scopeConfig->getValue("modernretail_import/settings/default_attribute_set");

        if ($valueFromConfig){
            $attributeSetCollection->addFieldToFilter("attribute_set_id",$valueFromConfig);
        }else {
            $attributeSetCollection->addFieldToFilter("attribute_set_name","Default");
        }

        if ($attributeSetCollection->count()==0) {
            throw new Exception("Select Default Attribute Set in System->Configuration->Magento Integrator Settings", 500);
        }

        return $attributeSetCollection->getFirstItem()->getId();

    }


    /**
     * CHeck if integrator can use system calls for some operations
     */
    public function canUseSystemExec(){

        return false;
        $canUse = true;
        if (strpos(ini_get("disable_functions"),"system")){
            $canUse = false;
        }
        if ($canUse===true && $this->scopeConfig->getValue(self::XML_CONFIG_USE_SYSTEM_CALLS)==0)
        {
            $canUse = false;
        }

        return $canUse;
    }

    /**
     * SOme magento installation used row_id instead entity_id.
     * We need return valid primary id field name
     */
    public function getEntityIdFieldName(){

        $catalog_product_entity = $this->resource->getTableName('catalog_product_entity');

        $sql = "describe $catalog_product_entity";
        $readConnection = $this->resource->getConnection('core_read');
        $result = $readConnection->query($sql)->fetchObject();
        return $result->Field;

    }

    public function getTableName($table){
        return $this->resource->getTableName($table);
    }


    public function isNeedToAutoClearCache(){


        $valueFromConfig = (boolean)$this->scopeConfig->getValue(self::XML_CONFIG_FLUSH_CACHE);

        if ($valueFromConfig===false){
            if ($_SERVER && array_key_exists('REQUEST_URI',$_SERVER) && strpos($_SERVER['REQUEST_URI'],'remote/execute')>0){
                return false;
            }
            foreach(debug_backtrace() as $d){
                if (is_array($d) && array_key_exists( 'class',$d) && $d['class']=='ModernRetail\Import\Model\Xml'){
                    return false;
                }
            }
        }
        return true;
    }



    public function getCurrentStoreId($attribute_code = null){

        $store_id = $this->storeManager->getStore()->getId();


        if ($attribute_code && in_array($attribute_code,['special_price','special_from_date','special_to_date'])===true ) return $store_id;


        if ($store_id==1) return 0;
        return $store_id;
    }


    public function getDefaultStoreId(){
       return $this->storeManager->getStore()->getId();
    }

    public function getIntegrationFileType($fileName){
        $type = 'Other';

            if(strpos($fileName, 'np_') !== false) {
                $type = 'New Products';
            }elseif(strpos($fileName, 'inv_') !== false){
                $type = 'Inventory(qty)';
            }elseif(strpos($fileName, 'sprc_') !== false){
                $type = 'Special Price';
            }

        return $type;
    }
 

    public function disableFlatData(){
        $this->registry->unregister('use_flat_data');
        $this->registry->register('use_flat_data', false);
        return $this;
    }

    public function enableFlatData(){
        $this->registry->unregister('use_flat_data');
        $this->registry->register('use_flat_data', true);

        return $this;
    }

    public function isFlatDataEnabled(){
        return $this->registry->registry('use_flat_data');

    }



}