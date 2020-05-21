<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Smartwave\Porto\Helper;

use Magento\Framework\Registry;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_objectManager;
    private $_registry;
    protected $_filterProvider;
    private $_checkedPurchaseCode;
    private $_messageManager;
    protected $_configFactory;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configFactory,
        Registry $registry
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_filterProvider = $filterProvider;
        $this->_registry = $registry;
        $this->_messageManager = $messageManager;
        $this->_configFactory = $configFactory;
        
        parent::__construct($context);
    }
    public function checkPurchaseCode($save = false) {
        if($this->isLocalhost()){
            return "localhost";
        }
        if(!$this->_checkedPurchaseCode){
            $code = $this->scopeConfig->getValue('porto_license/general/purchase_code');
            $code_confirm = $this->scopeConfig->getValue('porto_license/general/purchase_code_confirm');
            
            if($save) {
                $site_url = $this->scopeConfig->getValue('web/unsecure/base_url');
                $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $site_url));
                if(strpos($domain, "/"))
                    $domain = substr($domain, 0, strpos($domain, "/"));
                if(!$code || base64_encode($code) != $code_confirm) {
                    $this->curlPurchaseCode(base64_decode($code_confirm), "", "remove");
                }
                if($code) {
                    $result = $this->curlPurchaseCode($code, $domain, "add");
                    if(!$result || $result['result'] == 0) {
                        $this->_checkedPurchaseCode = "";
                        $code_confirm = "";
                        $this->_messageManager->getMessages(true);
                        $this->_messageManager->addWarning(__('Purchase code is not valid!'));
                    } else if($result['result'] == 1) {
                        $code_confirm = base64_encode($code);
                        $this->_checkedPurchaseCode = "verified";
                    } else {
                        $this->_checkedPurchaseCode = "";
                        $code_confirm = "";
                        $this->_messageManager->getMessages(true);
                        $this->_messageManager->addWarning(__($result['message']));
                    }
                } else {
                    $code_confirm = "";
                    $this->_checkedPurchaseCode = "";
                }
                $this->_configFactory->saveConfig('porto_license/general/purchase_code_confirm',$code_confirm,"default",0);
            } else {
                if($code && $code_confirm && base64_encode($code) == $code_confirm)
                    $this->_checkedPurchaseCode = "verified";
            }
        }
    
        return $this->_checkedPurchaseCode;
    }
    public function curlPurchaseCode($code, $domain, $act) {
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "http://www.portotheme.com/envato/verify_purchase_new.php?item=9725864&version=m2&code=$code&domain=$domain&act=$act");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PORTO-PURCHASE-VERIFY');

        // Decode returned JSON
        $result = json_decode( curl_exec($ch) , true );
        return $result;
    }
    public function isLocalhost() {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );
        
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }
    public function isAdmin() {
        $om = \Magento\Framework\App\ObjectManager::getInstance(); 
        $app_state = $om->get('\Magento\Framework\App\State');
        $area_code = $app_state->getAreaCode();
        if($area_code == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    public function getBaseLinkUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getModel($model) {
        return $this->_objectManager->create($model);
    }
    public function getCurrentStore() {
        return $this->_storeManager->getStore();
    }
    public function filterContent($content) {
        return $this->_filterProvider->getPageFilter()->filter($content);
    }
    public function getCategoryProductIds($current_category) {
        $category_products = $current_category->getProductCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_saleable', 1, 'left')
            ->addAttributeToSort('position','asc');
        $cat_prod_ids = $category_products->getAllIds();
        
        return $cat_prod_ids;
    }
    public function getPrevProduct($product) {
        $current_category = $product->getCategory();
        if(!$current_category) {
            foreach($product->getCategoryCollection() as $parent_cat) {
                $current_category = $parent_cat;
            }
        }
        if(!$current_category)
            return false;
        $cat_prod_ids = $this->getCategoryProductIds($current_category);
        $_pos = array_search($product->getId(), $cat_prod_ids);
        if (isset($cat_prod_ids[$_pos - 1])) {
            $prev_product = $this->getModel('Magento\Catalog\Model\Product')->load($cat_prod_ids[$_pos - 1]);
            return $prev_product;
        }
        return false;
    }
    public function getNextProduct($product) {
        $current_category = $product->getCategory();
        if(!$current_category) {
            foreach($product->getCategoryCollection() as $parent_cat) {
                $current_category = $parent_cat;
            }
        }
        if(!$current_category)
            return false;
        $cat_prod_ids = $this->getCategoryProductIds($current_category);
        $_pos = array_search($product->getId(), $cat_prod_ids);
        if (isset($cat_prod_ids[$_pos + 1])) {
            $next_product = $this->getModel('Magento\Catalog\Model\Product')->load($cat_prod_ids[$_pos + 1]);
            return $next_product;
        }
        return false;
    }

    public function getMasonryItemClass($arr) {
        $item_class = "";
        foreach ($arr as $key => $value) {
            $item_class .= ' ' . $key . '-' . $value;
        }
        return $item_class;
    }
}
