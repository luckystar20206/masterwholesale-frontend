<?php
/**
* Copyright © 2018 Porto. All rights reserved.
*/
namespace Smartwave\Megamenu\Helper;

use Magento\Framework\View\Result\PageFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_objectManager;
    protected $_categoryHelper;
    protected $_categoryFactory;
    protected $_categoryFlatConfig;
    protected $_filterProvider;
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager= $objectManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryFlatConfig = $categoryFlatState;
        $this->_categoryHelper = $categoryHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_filterProvider = $filterProvider;
        
        parent::__construct($context);
    }
    public function getBaseUrl($url_type=\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
    {
        return $this->_storeManager->getStore()->getBaseUrl($url_type);
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
    public function getFirstLevelCategories($sorted = false, $asCollection = false, $toLoad = true) {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
    public function getCategoryModel($id)
    {
        $_category = $this->_categoryFactory->create();
        $_category->load($id);
        
        return $_category;
    }
    public function getActiveChildCategories($category)
    {
        $children = [];
        $subcategories = $category->getChildrenCategories();
        foreach($subcategories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $children[] = $category;
        }
        return $children;
    }
    public function getBlockContent($content = '') {
        if(!$this->_filterProvider)
            return $content;
        return $this->_filterProvider->getBlockFilter()->filter(trim($content));
    }
    public function getResultPageFactory() {
        return $this->resultPageFactory;
    }
    public function getSubmenuItemsHtml($children, $level=0, $max_level=2)
    {
        $html = '';
        if(count($children) && ($level < $max_level)){
            $html .= '<ul';
            if($level == 0)
                $html .=' class="columns5"';
            $html .= '>';
            foreach($children as $child) {
                $html .= '<li class="menu-item level'.$level;
                $activeChildren = $this->getActiveChildCategories($child);
                
                if(count($activeChildren))
                    $html .= ' menu-parent-item';
                $html .= '">';
                
                $html .='<a href="'.$child->getUrl().'" data-id="'.$child->getId().'"><span>'.$child->getName().'</span></a>';
                if(count($activeChildren))
                    $html .= $this->getSubmenuItemsHtml($activeChildren, $level+1, $max_level);
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }
}
