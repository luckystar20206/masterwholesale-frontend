<?php
namespace Smartwave\Porto\Block;

class CategoryCollection extends \Magento\Framework\View\Element\Template
{

     protected $_categoryHelper;
     protected $categoryFlatConfig;
     protected $topMenu;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Theme\Block\Html\Topmenu $topMenu
    ) {

        $this->_categoryHelper = $categoryHelper;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->topMenu = $topMenu;
        parent::__construct($context);
    }
    /**
     * Return categories helper
     */   
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    /**
     * Return categories helper
     * getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
     * example getHtml('level-top', 'submenu', 0)
     */   
    public function getHtml()
    {
        return $this->topMenu->getHtml();
    }
    /**
     * Retrieve current store categories
     *
     * @param bool|string $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
     */    
   public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
    /**
     * Retrieve child store categories
     *
     */ 
    public function getChildCategories($category)
    {
        if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }
    
    public function getChildCategoryHtml($category, $icon_open_class="porto-icon-plus-squared", $icon_close_class="porto-icon-minus-squared") {
        $html = '';
        if($childrenCategories = $this->getChildCategories($category)) {
            $html .= '<ul>';
            $i = 0;
            foreach($childrenCategories as $childrenCategory) {
                if (!$childrenCategory->getIsActive()) {
                    continue;
                }
                $i++;
                $html .= '<li><a href="'.$this->_categoryHelper->getCategoryUrl($childrenCategory).'">'.$childrenCategory->getName().'</a>';
                $html .= $this->getChildCategoryHtml($childrenCategory, $icon_open_class, $icon_close_class);
                $html .= '</li>';
            }
            $html .= '</ul>';
            if($i > 0)
                $html .= '<a href="javascript:void(0)" class="expand-icon"><em class="'.$icon_open_class.'"></em></a>';
        }
        return $html;
    }
    
    public function getCategorySidebarHtml($icon_open_class="porto-icon-plus-squared", $icon_close_class="porto-icon-minus-squared") {
        $html = '';
        $categories = $this->getStoreCategories(true,false,true);
        $html .= '<ul class="category-sidebar">';
        foreach($categories as $category) {
            if (!$category->getIsActive()) {
            continue;
            }
            $html .= '<li>';
            $html .= '<a href="'.$this->_categoryHelper->getCategoryUrl($category).'">'.$category->getName().'</a>';
            $html .= $this->getChildCategoryHtml($category, $icon_open_class, $icon_close_class);
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '<script type="text/javascript">
            require([
                \'jquery\'
              ], function ($) {
                $(".category-sidebar li > .expand-icon").click(function(){
                    if($(this).parent().hasClass("opened")){
                        $(this).parent().children("ul").slideUp();
                        $(this).parent().removeClass("opened");
                        $(this).children(".'.$icon_close_class.'").removeClass("'.$icon_close_class.'").addClass("'.$icon_open_class.'");
                    } else {
                        $(this).parent().children("ul").slideDown();
                        $(this).parent().addClass("opened");
                        $(this).children(".'.$icon_open_class.'").removeClass("'.$icon_open_class.'").addClass("'.$icon_close_class.'");
                    }
                });
            });
        </script>';
        return $html;
    }
}
