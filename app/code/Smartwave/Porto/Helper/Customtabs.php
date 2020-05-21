<?php
namespace Smartwave\Porto\Helper;

class Customtabs extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_filterProvider;
    protected $_storeManager;
    protected $_blockFactory;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {

        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $storeManager;
        
        parent::__construct($context);
    }
    
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function subval_sort($a,$subkey) {
        foreach($a as $k=>$v) {
            $b[$k] = strtolower($v[$subkey]);
        }
        asort($b);
        foreach($b as $key=>$val) {
            $c[] = $a[$key];
        }
        return $c;
    }
    public function checkShowingTab($tab_cat_ids, $parent_cat_ids, $tab_prod_skus, $prod_sku) {
		if(!$tab_cat_ids && !$tab_prod_skus)
            return true;
        $tab_cat_ids = explode(",",$tab_cat_ids);
        $tab_prod_skus = explode(",",$tab_prod_skus);
        if(count($tab_prod_skus)>0 && count($tab_cat_ids)>0){
            if(in_array($prod_sku, $tab_prod_skus) || count(array_intersect($tab_cat_ids, $parent_cat_ids))>0)
                return true;
        }
        if(count($tab_prod_skus)>0 && in_array($prod_sku, $tab_prod_skus))
            return true;
        if(count($tab_cat_ids)>0 && count(array_intersect($tab_cat_ids, $parent_cat_ids))>0)
            return true;
        
        return false;
    }

    public function getBlockContent($content = '') {
        if(!$this->_filterProvider)
            return $content;
        return $this->_filterProvider->getBlockFilter()->filter(trim($content));
    }
    public function getCustomTabs($product){
        $cms_tabs = $this->getConfig('porto_settings/product/custom_cms_tabs');
        $attr_tabs = $this->getConfig('porto_settings/product/custom_attr_tabs');
        $_sku = $product->getSku();
        if($cms_tabs)
            $cms_tabs = unserialize($cms_tabs);
        if($attr_tabs)
            $attr_tabs = unserialize($attr_tabs);
        
        $parents = array();
        if(count($cms_tabs)>0 || count($attr_tabs)>0) {
            foreach($product->getCategoryCollection() as $parent_cat) {
                $parents[] = $parent_cat->getId();
            }
        }
        $store_id = $this->_storeManager->getStore()->getId();
        $custom_tabs = array();
        if(count($cms_tabs)>0){
            foreach($cms_tabs as $_item) {
                if($this->checkShowingTab($_item['category_ids'],$parents,$_item['product_skus'],$_sku)){
                    $block_id = $_item['staticblock_id'];
                    if(!$block_id)
                        continue;
                    $block = $this->_blockFactory->create();
                    $block->setStoreId($store_id)->load($block_id);
                    
                    if(!$block) continue;
                    
                    $block_content = $block->getContent();
                    
                    if(!$block_content) continue;
                            
                    $content = $this->_filterProvider->getBlockFilter()->setStoreId($store_id)->filter($block_content);
                    $arr = array();
                    $arr['tab_title'] = $_item['tab_title'];
                    $arr['tab_content'] = $content;
                    $arr['sort_order'] = (!$_item['sort_order'] || !is_numeric($_item['sort_order']))?0:$_item['sort_order'];
                    $custom_tabs[] = $arr;
                }
            }
        }
        if(count($attr_tabs)>0){
            foreach($attr_tabs as $_item) {
                if($this->checkShowingTab($_item['category_ids'],$parents,$_item['product_skus'],$_sku)){
                    $attr_code = $_item['attribute_code'];
                    
                    $attribute = $product->getResource()->getAttribute($attr_code);
                    if(!$attribute)
                        continue;
                    $attr_value = $attribute->getFrontend()->getValue($product);
                    if(!$attr_value) continue;
                    
                    $content = $this->_filterProvider->getBlockFilter()->setStoreId($store_id)->filter($attr_value);
                    $arr = array();
                    $arr['tab_title'] = $_item['tab_title'];
                    $arr['tab_content'] = $content;
                    $arr['sort_order'] = (!$_item['sort_order'] || !is_numeric($_item['sort_order']))?0:$_item['sort_order'];
                    $custom_tabs[] = $arr;
                }
            }
        }
        if(count($custom_tabs)>0)
            $custom_tabs = $this->subval_sort($custom_tabs,'sort_order');
        
        return $custom_tabs;
    }
}
