<?php
namespace ModernRetail\Import\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class AfterSettingsSave  implements ObserverInterface{

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


        $configurableMapping = $this->scopeConfig->getValue("modernretail_import/configurable_mapping/json_value");
        $configurableMapping = json_decode($configurableMapping,true);

        $simpleMapping = $this->scopeConfig->getValue("modernretail_import/simple_mapping/json_value");
        $simpleMapping = json_decode($simpleMapping,true);

        $updateMapping = $this->scopeConfig->getValue("modernretail_import/update_mapping/json_value");
        $updateMapping = json_decode($updateMapping,true);

        $useCategories = $this->scopeConfig->getValue("modernretail_import/settings/use_categories");
        $newProductStatus = $this->scopeConfig->getValue("modernretail_import/settings/new_products_status");

        $xml = '<?xml version="1.0"?><config>';
        $xml.= ' <!-- "1" to allow create and assign categories -->
        		 <use_categories>'.$useCategories.'</use_categories>';

        $xml.= '   <!-- 1 - Enabled... 2-Disabled -->
      		  <new_products_status>'.$newProductStatus.'</new_products_status>';



        $xml.= ' <!-- Attribute Mappings for Simple products -->
        <simple_product_attributes>';
        foreach($simpleMapping as $tagName=>$attribute){
            if ($attribute['status']===false) continue;
            $xml.="<attribute tag=\"{$tagName}\" attribute_code=\"{$attribute['attribute']}\" ";
            if ($attribute['is_configurable']===true){
                $xml.=" configurable='true'";
            }
            $xml.="/>";
        }
        $xml .="</simple_product_attributes>";


        $xml.= ' <!-- Attribute Mappings for configurable products -->
        <configurable_product_attributes>';
        foreach($configurableMapping as $tagName=>$attribute){
            if ($attribute['status']===false) continue;
            $xml.="<attribute tag=\"{$tagName}\" attribute_code=\"{$attribute['attribute']}\" ";
            if ($attribute['is_configurable']===true){
                $xml.=" configurable=\"true\"";
            }
            $xml.="/>";
        }
        $xml .="</configurable_product_attributes>";

        $xml.= ' <!-- Attribute Mappings for update products -->
        <update>';
        foreach($updateMapping as $tagName=>$attribute){
            if ($attribute['status']===false) continue;
            $xml.="<{$tagName}>{$attribute['attribute']}</{$tagName}>";
        }
        $xml .="</update>";

        $xml.="</config>";

        file_put_contents($this->helper->getPath()."/config.xml", $xml);


    }

}