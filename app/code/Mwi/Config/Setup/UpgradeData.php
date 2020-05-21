<?php

namespace Mwi\Config\Setup;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config as EmailConfig;
use Magento\Customer\Setup\CustomerSetup;

class UpgradeData implements UpgradeDataInterface
{

    private $config;

    protected $indexerRegistry;

    protected $reinitableConfig;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var PageInterface
     */
    protected $pageInterface;

    /**
     * @var PageInterfaceFactory
     */
    protected $pageInterfaceFactory;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;
    /**
     * @var BlockInterfaceFactory
     */
    protected $blockInterfaceFactory;

    /**
     * @var BlockInterface
     */
    protected $blockInterface;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var EavSetupFactory
     */
    protected $writeInterface;

    protected $setup;


    private $customerSetupFactory;
    

    protected $_collectionFactory;

    protected $_emailConfig;
    

    public function __construct(
        Config $scopeConfig,
        IndexerRegistry $indexerRegistry,
        ReinitableConfigInterface $reinitableConfigInterface,
        BlockRepositoryInterface $blockRepository,
        BlockInterfaceFactory $blockInterfaceFactory,
        BlockInterface $blockInterface,
        EavSetupFactory $eavSetupFactory,
        WriterInterface $writerInterface,
        PageRepositoryInterface $pageRepository,
        PageInterface $pageInterface,
        PageInterfaceFactory $pageInterfaceFactory,
        CustomerSetupFactory $customerSetupFactory,
        CollectionFactory $collectionFactory,
        EmailConfig $emailConfig
    ) {
        $this->config = $scopeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->reinitableConfig = $reinitableConfigInterface;
        $this->blockRepository = $blockRepository;
        $this->blockInterfaceFactory = $blockInterfaceFactory;
        $this->blockInterface = $blockInterface;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->writerInterface = $writerInterface;
        $this->pageRepository = $pageRepository;
        $this->pageInterface = $pageInterface;
        $this->pageInterfaceFactory = $pageInterfaceFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_emailConfig = $emailConfig;

    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        $this->setup->startSetup();

         /*
         * Update config values
         */

       
        foreach ($this->_getConfigArray() as $config) {
            if (isset($config['version']) && version_compare($context->getVersion(), $config['version'], '<')) {
                $this->setConfigData($config['path'], $config['value'],
                    isset($config['scope']) ? $config['scope'] : 'default',
                    isset($config['scope_id']) ? $config['scope_id'] : 0
                );
            }
        }


        /*
         * Update static blocks
         */
        foreach ($this->_getBlockArray() as $block) {
            if (isset($block['version']) && version_compare($context->getVersion(), $block['version'], '<')) {
                $this->createCmsBlock($block['identifier'], $block['content'], $block['title'], $block['stores']);
            }
        }


        /*
         * Create / Update cms pages
         */
        foreach ($this->_getPageArray() as $block) {
            if (isset($block['version']) && version_compare($context->getVersion(), $block['version'], '<')) {
                $this->createCmsPage($block['identifier'], $block['content'], $block['title'], $block['content_heading'], $block['page_layout'], $block['stores']);
            }
        }

        if ( version_compare($context->getVersion(), '0.0.7', '<')) {
             /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'product_options_type',
                [
                    'group' => 'Product Details',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Product Options Type',
                    'input' => 'select',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => 'configurable',
                    'option' => ['values' => ['dropdown', 'radio']]
                ]
            );
        }
       
            

        $this->setup->endSetup();
    }
   
   
    public function setConfigData($path, $value, $scope = 'default', $scopeId = 0){
        $this->writerInterface->save($path, $value, $scope, $scopeId);
    }

   
    /**
     * Create CMS blocks
     */
    public function createCmsBlock($id, $html = '', $title = '', $stores = [0])
    {        
        try {
            $cmsBlock = $this->blockRepository->getById($id);
            if ($title){
                $cmsBlock->setTitle($title);
            }
            if ($html){
                $cmsBlock->setContent($html);
            }
            
        } catch (NoSuchEntityException $ex) {
            $cmsBlock = $this->blockInterfaceFactory->create();
            
            $cmsBlock->setTitle($title)
                     ->setIdentifier($id)
                     ->setStores($stores)
                     ->setContent($html);        
        }
        
        $this->blockRepository->save($cmsBlock);
    }


    /**
     * Create CMS pages
     */
    public function createCmsPage($id, $html = '', $title = '', $content_heading = '', $page_layout = '1column', $stores = [0])
    {        
        try {
            $cmsPage = $this->pageRepository->getById($id);
            if ($title){
                $cmsPage->setTitle($title);
            }
            if ($content_heading){
                $cmsPage->setContentHeading($content_heading);
            }
            if ($page_layout){
                $cmsPage->setPageLayout($page_layout);
            }
            if ($html){
                $cmsPage->setContent($html);
            }
            
        } catch (NoSuchEntityException $ex) {
            $cmsPage = $this->pageInterfaceFactory->create();
            $cmsPage->setTitle($title)
                     ->setIdentifier($id)
                     ->setStores($stores)
                     ->setContent($html);  

            
            if ($content_heading){
                $cmsPage->setContentHeading($content_heading);
            }
            if ($page_layout){
                $cmsPage->setPageLayout($page_layout);
            }
            
             
        }
        
        $this->pageRepository->save($cmsPage);
    }


    /*
     * You can add scope and scope_id to array:
     *      'scope' => 'default', 'scope_id' => 0,
     * Note:
     *      if you want to use heredoc notation for value <<<HTML / HTML this must be the last array element without
     *      ending colon or semicolon to work properly
     *
     * @return Array with configurations
     */
    protected function _getConfigArray(){
        
        return array(
                array(
                    'version' => '0.0.4',
                    'path' => 'bss_quickview/seting_theme/product_image_wrapper',
                    'value' => 'product-item-photo'
                ),
                array(
                    'version' => '0.0.5',
                    'path' => 'bss_quickview/success_popup_design/background_color',
                    'value' => '0088CC'
                ),
                array(
                    'version' => '0.0.5',
                    'path' => 'porto_settings/custom_settings/custom_style',
                    'value' => 'table.desc_spec_tbl{border-collapse:collapse}
table.desc_spec_tbl td{border:1px solid #dcdcdc}
.product.description ul{list-style:disc}
.page-header.type2.header-newskin .main-panel-top{border-bottom:1px solid rgba(248,248,248,0.2)}
.page-header.type2.header-newskin .main-panel-inner{border:none}
#phone-hrs{font-size:1.4em!important;color:#636363;padding-right:15px!important}
#phone-hrs a{color:#08C!important}
.page-header.type2.header-newskin .custom-block{width:100%!important;text-align:right!important;top:90%!important;right:0!important}
h2.side-menu-title{margin:0;background-color:#FF7B0D;color:#fff;font-size:13px;font-weight:700;line-height:1;padding:14px 15px;border-radius:5px 5px 0 0;border-bottom:1px solid #ddd}
.filterproduct-title{color:#fff!important;background-color:#08c!important;padding-left:20px!important}
.products-grid.columns4{margin-left:0!important;margin-right:0!important}
.home-side-menu h2.side-menu-title{color:#fff!important}
.page-header.type2.header-newskin .minicart-wrapper .action.showcart:before,.page-header.type2.header-newskin .minicart-wrapper .action.showcart.active:before{font-size:33px;color:#08C!important}
.page-header.type2.header-newskin .minicart-wrapper .action.showcart{padding-right:17px}
.page-header.type2.header-newskin .minicart-wrapper .action.showcart .counter.qty{margin-top:-21px;background-color:#ff5b5b}
.page-header.type2.header-newskin .minicart-wrapper .action.showcart:after{right:-6px}
.homepage-bar{border:none;background-color:transparent}
.homepage-bar .col-lg-4{border-color:#fff;padding-top:14px;padding-bottom:15px}
.homepage-bar [class*=" porto-icon-"],.homepage-bar [class^="porto-icon-"]{color:#465157}
.homepage-bar .text-area{display:inline-block;vertical-align:middle;text-align:left;margin-left:5px}
.homepage-bar h3{font-size:14px;font-weight:600;color:#465157;line-height:19px}
.homepage-bar p{font-size:13px;font-weight:300;color:#839199;line-height:19px}
.owl-theme .owl-dots .owl-dot span{width:13px;height:13px;border-radius:100%;border:solid 2px #d5d5d5;background:none;position:relative;margin:5px 2px}
#banner-slider-demo-9.owl-bottom-narrow .owl-controls{text-align:left;padding-left:28px}
#banner-slider-demo-9.owl-theme .owl-dots .owl-dot span{border:2px solid rgba(0,0,0,0.2);background:none}
#banner-slider-demo-9.owl-theme .owl-dots .owl-dot.active span,#banner-slider-demo-9.owl-theme .owl-dots .owl-dot:hover span{border-color:#fff;background:none}
.owl-theme .owl-dots .owl-dot.active span,.owl-theme .owl-dots .owl-dot:hover span{border-color:#05131c;background:none}
.owl-theme .owl-controls .owl-dot.active span:before,.owl-theme .owl-dots .owl-dot:hover span:before{content:"";position:absolute;left:3px;top:3px;right:3px;bottom:3px;background-color:#05131c;border-radius:100%}
#banner-slider-demo-9.owl-theme .owl-dots .owl-dot.active span:before,#banner-slider-demo-9.owl-theme .owl-dots .owl-dot:hover span:before{background-color:#fff}
.owl-theme .owl-dots .owl-dot.active span:before,.owl-theme .owl-dots .owl-dot:hover span:before{background-color:#05131c}
.block.block-subscribe.home-sidebar-block{border:none;background-color:#f4f4f4;text-align:center;border-radius:2px!important}
.block.block-subscribe.home-sidebar-block .block-title strong{font-size:17px;font-weight:700;color:#05131c}
.block.block-subscribe.home-sidebar-block .block-content p{line-height:24px;letter-spacing:.001em;color:#4a505e;font-size:14px}
.block.block-subscribe.home-sidebar-block .newsletter .control input{height:45px;border-color:#e4e4e4;padding-right:10px;border-radius:3px;color:#05131c;text-transform:uppercase}
.block.block-subscribe.home-sidebar-block button.subscribe{width:100%;margin:7px 0 0;height:auto;position:relative;left:auto;right:auto;top:auto;border-radius:5px;background-color:inherit}
.block.block-subscribe.home-sidebar-block button.subscribe span{height:45px;text-transform:uppercase;background-color:#05131c;border:none;border-radius:3px;font-size:12px;letter-spacing:.005em;color:#fff;font-family:"Oswald";line-height:45px;display:block}
#testimonials-slider-demo-9{padding:22px;border:solid 2px #0188cc;border-radius:2px}
.cms-index-index .testimonial-author{margin:8px 0 0}
.cms-index-index .testimonial-author .img-thumbnail{border:none;padding:0;margin-right:20px;border-radius:100%!important;overflow:hidden}
.cms-index-index blockquote.testimonial{background-color:transparent;color:#62615e;font-size:14px;font-style:normal;line-height:24px;font-weight:400;font-family:"Open Sans";margin:0 -10px;padding:15px 30px 15px 43px;width:100%;float:left;margin-top:13px}
.cms-index-index blockquote.testimonial:before{color:#0188cc;font-family:"porto";font-weight:400;font-size:54px}
.cms-index-index blockquote.testimonial:after{color:#0188cc;font-family:"porto";font-weight:400;font-size:54px;right:-2px}
.cms-index-index .testimonial-author p{line-height:20px}
.cms-index-index .testimonial-author p >strong{text-transform:uppercase;font-size:13px;font-weight:700;letter-spacing:.0025em;color:#2b2b2d}
.cms-index-index blockquote.testimonial p{line-height:24px;letter-spacing:.001em}
#testimonials-slider-demo-6.owl-theme .owl-controls{text-align:left;padding-left:32px}
.recent-posts .item{padding-top:40px}
.recent-posts .post-date{display:block;float:none;text-align:left}
.recent-posts .post-date .long-date{font-size:13px;font-weight:700;color:#0188cc;line-height:22px;text-transform:uppercase}
.recent-posts .postTitle{min-height:auto}
.recent-posts .postTitle h2{margin:0}
.recent-posts .postTitle h2 a{font-size:17px;font-weight:600;line-height:22px;color:#2b2b2d}
.recent-posts .postContent{margin:0;padding:0}
.recent-posts .postContent>p{line-height:27px;letter-spacing:.001em}
.recent-posts a.readmore{display:none}
.recent-posts .owl-controls{text-align:left}
.filterproduct-title{background:none;font-size:17px;color:#2b2b2d}
.filterproduct-title .content{background:none;padding:0}
.owl-top-narrow .owl-theme .owl-controls .owl-dots{margin-top:0}
.small-list.products-grid .product-item .product-item-name{font-size:14px;font-weight:400;letter-spacing:.005em}
.small-list.products-grid .product-item .product-reviews-summary{margin-top:0}
.shop-features [class*=" porto-icon-"],.shop-features [class^="porto-icon-"]{color:#0188cc;border-color:#0188cc}
.shop-features h3{font-size:14px;font-weight:700}
.shop-features p{color:#4a505e;line-height:27px;font-weight:400}
.shop-features a{font-family:"Oswald";font-size:12.5px;text-transform:uppercase;color:#2b2b2d;letter-spacing:.0025em;line-height:26px;border:solid 1px #efefef;padding:7px 28px;background:none;box-shadow:none}
.shop-features a:hover{background:none;color:#2b2b2d;border-color:#efefef}
.owl-top-narrow{margin:0 -10px}
.owl-top-narrow .owl-theme .owl-controls{right:8px}
.owl-top-narrow .owl-carousel .owl-item > .item{padding:10px}
@media (min-width: 768px) {
.catalog-category-view .page-main,.catalog-product-view .page-main{padding-top:0}
}
.breadcrumbs li.home a{width:15px;display:inline-block;text-indent:30px;overflow:hidden;float:left;position:relative}
.breadcrumbs li.home a:after{content:"\e883";font-family:"porto-icons";position:absolute;left:0;top:0;text-indent:0}
.sidebar.sidebar-main{position:relative}
.block-category-list .block-title,.filter-options-title,.filter .filter-current-subtitle{border:none;background-color:transparent;padding:16px 20px 4px;font-size:15px;text-transform:uppercase;font-weight:600;color:#21293c;letter-spacing:.01em}
.block-category-list .block-title{padding:0 20px 0}
.block-category-list .block-title strong{font-weight:600}
.block-category-list .block-content,.filter-options-content{border:none;background-color:transparent;padding:10px 20px 26px;border-bottom:#efefef solid 1px;position:relative;z-index:2;border-radius:0}
.filter-current .items{border:none;background-color:transparent;position:relative;z-index:2}
.filter-current .item{padding-left:20px}
.filter-current .action.remove{right:20px;color:#21293c}
.filter-actions{border:none;background-color:transparent;border-bottom:#efefef solid 1px;position:relative;z-index:2;padding-right:20px}
.filter-actions a{color:#858585}
.filter-options-title:after{content:"\f803";border:none;color:#21293c;font-size:17px;margin-top:-6px}
.filter-options-title:hover:after{background:none;color:#21293c}
.active > .filter-options-title:after{content:"\f800"}
#ln_slider_price.ui-slider-horizontal{height:3px;box-shadow:none}
#ln_slider_price .ui-slider-handle{width:12px;height:12px;border-radius:100%}
.sidebar-title{font-size:15px;font-weight:600;color:#21293c;letter-spacing:.01em;margin-bottom:20px;padding-left:20px;padding-right:20px}
.porto-icon-left-open-huge:before{content:"\f802";color:#21293c}
.porto-icon-right-open-huge:before{content:"\f801";color:#21293c}
.sidebar .owl-top-narrow .owl-theme .owl-controls{top:-40px;right:3px}
.sidebar .owl-top-narrow .owl-theme .owl-controls .owl-nav div :before{color:#21293c}
.sidebar .product-items .product-item-info .product-item-photo{max-width:25.19%}
.sidebar .product-items .product-item-details{margin-left:calc(26% + 15px)}
.sidebar .product-items .product-item-name a{font-size:12px;color:#5b5b5f;font-weight:400}
.sidebar .sidebar-filterproducts{margin-bottom:30px;padding-bottom:40px;background:none;border-bottom:#efefef solid 1px}
.sidebar .product-items .product-item .product-reviews-summary{display:block}
.sidebar-filterproducts.custom-block + h2{font-size:15px!important;text-transform:uppercase;font-weight:600;color:#21293c!important;letter-spacing:.01em;padding:0 20px}
.sidebar-filterproducts.custom-block + h2 +h5{font-family:"Open Sans"!important;font-weight:600!important;font-size:14px!important;color:#7a7d82!important;letter-spacing:.022em;padding:0 20px}
.sidebar-filterproducts.custom-block + h2 + h5 + p{color:#21293c!important;font-size:15px!important;letter-spacing:.01em;padding:0 20px}
.sidebar .custom-block{padding:0 20px}
.category-boxed-banner.owl-theme .owl-controls{bottom:0}
.page-products .toolbar .limiter{display:block}
.page-with-filter .toolbar-amount{display:none}
.full-width-image-banner{height:300px}
.full-width-image-banner:after{display:none}
.full-width-image-banner h2{font-size:36px;font-weight:900;letter-spacing:-.025em;text-transform:uppercase;line-height:38px}
.full-width-image-banner p{font-size:18px;line-height:38px;font-weight:700;text-transform:uppercase}
.full-width-image-banner .btn-default{font-size:14px;line-height:25px;letter-spacing:.025em;padding:10px 20px;background-color:#010204;color:#fff;font-family:"Oswald";text-transform:uppercase;border-radius:2px;margin-top:31px}
.page-products .toolbar .limiter .limiter-text{display:none}
.modes-mode.active{border:none;background:none;color:#111}
.modes-mode,.modes-mode:hover{border:none;background:none;color:#111;width:15px}
.toolbar select{border:1px solid #e4e4e4;height:37px;color:#7a7d82;font-weight:400;font-size:14px;text-transform:capitalize;padding:0 10px;padding-right:30px;line-height:31px}
.toolbar-sorter .sorter-action{margin-top:6px;color:#21293c}
.toolbar-sorter .sorter-action:before{color:#21293c}
.pages a.page,.pages strong.page,.pages .action{width:32px;line-height:32px}
.products-grid + .toolbar.toolbar-products{border-top:solid 1px #efefef;padding-top:25px}
.filterproducts.products .product-item .product-item-photo{border:solid 1px #ececec}
.product-item .product-reviews-summary{background:none}
.price-box .price{font-family:"Oswald";font-weight:400;font-size:18px;color:#465157}
.old-price .price{font-size:13px;color:#999}
.catalog-product-view .sidebar .custom-block{border:none;color:#6b7a83;padding-bottom:0;margin-bottom:33px;background:none}
.catalog-product-view .sidebar .custom-block-1>div i{color:#08c;border:none;font-size:40px;float:left}
.catalog-product-view .sidebar .custom-block-1>div{min-height:65px;clear:both;padding:18px 0;border-bottom:solid 1px #dee5e8;margin-bottom:0}
.catalog-product-view .sidebar .custom-block-1>div:last-child{border-bottom-width:0}
.block-manufacturer{text-align:center;padding:10px 20px 0;margin-bottom:0}
.block-manufacturer hr{border-color:#dee5e8;margin-bottom:0}
.catalog-product-view .sidebar .custom-block-1>div h3{font-size:14px;font-weight:600;line-height:20px;letter-spacing:.005em;color:#6b7a83;margin-left:80px}
.block.related{padding:0 20px}
.block .title strong{font-size:15px;font-weight:600;color:#21293c;letter-spacing:.01em;margin-bottom:20px!important;padding-top:0;text-transform:uppercase}
.block.related .product-items .product-item-actions{display:none}
.product-info-main .page-title-wrapper h1{font-size:25px;font-weight:600;letter-spacing:-.01em;color:#21293c;margin:3px 0 15px}
.prev-next-products a{color:#555}
.product-reviews-summary .reviews-actions a{line-height:20px;font-size:14px;color:#bdbdbd}
.product-info-main .product.overview{font-size:14px;font-weight:400;letter-spacing:.005em;line-height:27px;border-bottom:solid 1px #dae2e6}
.product.media{padding-right:12px}
.fotorama__stage__shaft{border:none}
.fotorama__nav--thumbs .fotorama__thumb{border-color:#dae2e6}
.product-options-bottom .price-box .price-container .price,.product-info-price .price-box .price-container .price{font-family:"Oswald";font-size:21px;font-weight:700;letter-spacing:.005em}
.product-info-main .product-info-price .old-price .price-wrapper .price{font-size:16px;color:#2b2b2d;font-weight:400}
.product-info-main .fieldset > .field.qty,.product-info-main .nested.options-list > .field.qty{position:relative;width:106px}
.product-info-main .qty.field .control,.product-info-main .qty.field .qty-changer{margin-left:29px}
.product-info-main .qty.field .qty-changer > a{position:absolute;top:0;left:0;height:43px;width:30px;line-height:41px;text-align:center;margin:0;border-color:#dae2e6}
.product-info-main .qty.field .qty-changer > a:first-child{left:auto;right:4px}
.product-info-main .box-tocart .input-text.qty{font-family:"Oswald";display:inline-block;vertical-align:middle;height:43px;width:44px!important;font-size:14px;font-weight:400;text-align:center;color:#61605a;margin:0;border-color:#dae2e6}
.product-info-main .qty.field .qty-changer > a .porto-icon-up-dir:before{content:"\f882"}
.product-info-main .qty.field .qty-changer > a .porto-icon-down-dir:before{content:"\f883"}
.catalog-product-view:not(.weltpixel-quickview-catalog-product-view):not(.weltpixel_quickview-catalog_product-view) .box-tocart .action.tocart{height:43px;font-size:14px;letter-spacing:.05em;font-weight:400}
.box-tocart .action.tocart:before{content:"\e87f";font-family:"porto-icons";margin-right:7px;font-size:16px}
.action.primary,.action.primary:active{font-family:"Oswald";letter-spacing:1px;text-transform:uppercase}
.product-addto-links .action.towishlist,.product-addto-links .action.tocompare,.moved-add-to-links .action.mailto.friend,.product-social-links .action.mailto.friend{width:43px;height:43px;line-height:41px}
.product.data.items > .item.content{background-color:#fff;box-shadow:none;border:none;border-top:#dae2e6 solid 1px;font-size:14px;font-weight:400;letter-spacing:.005em;line-height:27px}
.main-upsell-product-detail .block.upsell .title strong{background:none}
.block.upsell .title{background:none;border-bottom:#e1e1e1 solid 1px;font-weight:700;margin-bottom:16px;padding-bottom:10px;text-transform:uppercase;text-align:left}
.block.upsell .title strong{font-size:14px;font-weight:400;font-family:"Oswald";color:#302e2a}
.review-ratings .rating-label{display:block}
.footer-middle{padding:62px 0 42px}
.footer-ribbon{margin:-78px 0 25px}
.footer-middle p{font-size:13px;line-height:20px;margin-bottom:0}
.footer-middle .block .block-title{margin-bottom:15px}
.footer-middle .block .block-title strong{font-size:16px;font-weight:700;text-transform:uppercase}
.footer-middle ul.links li,.footer-middle ul.features li{padding:6px 0}
.footer-container .validation-advice{position:absolute}
.footer-middle .block.newsletter .form.subscribe{max-width:400px}
.footer-middle .block.newsletter .control:before{line-height:48px;margin-left:20px}
.footer-middle .block.newsletter .control{position:relative}
.footer-middle .block.newsletter .control input{background-color:#fff;color:#686865;height:48px;border:none;font-size:14px;padding-left:10px}
.footer-middle .block.newsletter .control div.mage-error{position:absolute;bottom:-22px}
.footer-middle .block.newsletter .action.subscribe{height:48px;text-transform:uppercase;padding:0 22px}
.footer-middle .block-bottom{border-top:1px solid #3d3d38;text-align:left;padding:27px 0;overflow:hidden}
.footer-middle .social-icons a{background-image:none;background-color:#33332f;text-indent:0;color:#fff;border-radius:0;font-size:15px;width:37px;height:37px;text-align:center;margin-left:0;margin-right:4px;float:left;line-height:35px}
.footer-middle .contact-info li:first-child{border-top-width:0}
.footer-middle .contact-info li{padding:9px 0}
.footer-middle .contact-info i{color:#e1ddc3!important;font-size:26px;line-height:28px}
.footer-middle .contact-info p{line-height:1}
.footer-middle .contact-info b{font-weight:400;font-size:13px;margin-bottom:7px;display:inline-block}
.footer-bottom{padding:18px 0}
.footer-bottom address{float:left}
.footer-bottom .container{position:relative}
.footer-bottom .container:before{content:"";position:absolute;background-color:#3d3d38;left:15px;right:15px;top:-18px;height:1px;width:calc(100% - 30px)}
@media (max-width: 991px) {
.footer .block .block-content{margin-bottom:30px}
.footer-middle .block-content{min-width:auto!important;width:100%}
}
@media (max-width: 767px) {
.navigation.sw-megamenu > .sticky-logo{display:none}
.page-header.type2.header-newskin .custom-block{display:none}
.homepage-bar .col-lg-4{text-align:left!important}
#banner-slider-demo-9{margin-bottom:20px}
.sidebar.sidebar-main{position:static}
.page-products .toolbar .limiter{display:none}
.product.data.items{margin:0}
.prev-next-products .product-nav.product-next .product-pop{margin:0}
.prev-next-products .product-nav.product-prev .product-pop{left:-20px}
.product-info-main .fieldset > .field.qty{margin-bottom:20px}
.fieldset > .actions{vertical-align:top}
.catalog-product-view .sidebar .custom-block{padding:0}
.footer-middle{padding:62px 0 0;margin-bottom:-20px}
.footer .block .block-content{margin-bottom:30px}
.footer-middle .block-content{float:none!important}
.footer-middle .social-icons{overflow:hidden;float:none!important}
.footer-bottom .custom-block.f-right{margin-left:0}
}
.page-products .sorter{float:left}
.modes{float:right;margin-right:0;margin-left:20px;margin-top:7px}
.modes-mode:before{content:"\e880";font-size:14px}
.mode-list:before{content:"\e87b";font-size:14px}
.products.wrapper ~ .toolbar .limiter{float:left}
.products.wrapper ~ .toolbar .pages{float:right}
@media (min-width: 768px) {
.page-header.type2.header-newskin .minicart-wrapper{background-color:transparent;width:81px;height:41px;text-align:center;box-shadow:none;border-radius:0;border:none}
.home-side-menu{background-color:transparent;border-color:#dae2e6;border-radius:2px}
.home-side-menu h2.side-menu-title{color:#465157;font-size:14.5px;font-weight:700;letter-spacing:.001em}
.navigation.side-megamenu a,.navigation.side-megamenu a:hover{color:#465157}
.sw-megamenu.navigation.side-megamenu li.level0.parent > a:after{color:#838b90}
.sw-megamenu.navigation.side-megamenu li.level0.parent:hover > a:after{color:#fff}
.sw-megamenu.navigation.side-megamenu li.level0 > .submenu{border:solid 1px #dae2e6;box-shadow:0 3px 15px -2px rgba(0,0,0,0.3);padding:10px 0 10px 15px}
.sw-megamenu.navigation.side-megamenu li.level0 > .submenu:before,.sw-megamenu.navigation.side-megamenu li.level0 > .submenu:after{border-bottom-style:solid;content:"";display:block;height:0;position:absolute;width:0}
.sw-megamenu.navigation.side-megamenu li.level0 > .submenu:before{border:8px solid;border-color:transparent #fff transparent transparent;z-index:3;left:-16px;top:11px}
.sw-megamenu.navigation.side-megamenu li.level0 > .submenu:after{border:9px solid;border-color:transparent #dae2e6 transparent transparent;z-index:2;left:-18px;top:10px;right:auto}
.sw-megamenu.navigation li.level0.fullwidth .submenu li.level1 > a,.sw-megamenu.navigation li.level0.staticwidth .submenu li.level1 > a{font-size:13px;font-weight:700;color:#434d53;letter-spacing:-.001em;margin-top:9px}
.sw-megamenu.navigation li.level0.fullwidth .submenu a,.sw-megamenu.navigation li.level0.staticwidth .submenu a,.sw-megamenu.navigation li.level0.classic .submenu a{font-size:13px;font-weight:600;color:#696969;line-height:inherit}
.sidebar.sidebar-main:before{content:"";position:absolute;left:0;right:20px;border:solid 1px #dae2e6;top:0;bottom:0;border-radius:2px}
.product.data.items > .item.title{padding:10px 30px 10px 0}
.product.data.items > .item.title > .switch{font-size:14px;font-weight:700;color:#818692;text-transform:uppercase;border:none;border-radius:0;line-height:30px;background:none;padding:0}
.product.data.items > .item.title:not(.disabled) > .switch:focus,.product.data.items > .item.title:not(.disabled) > .switch:hover{background:none;color:#818692}
.product.data.items > .item.title.active > .switch,.product.data.items > .item.title.active > .switch:focus,.product.data.items > .item.title.active > .switch:hover{color:#21293c;position:relative;border-bottom:#08C solid 2px}
.product.data.items > .item.content{padding:35px 0 0;margin-top:45px}
}
@media (min-widtH: 768px) {
.page-header .switcher .options .action.toggle{color:#fff}
}
.products-grid .product-item .product-item-info .product-item-photo > a:not(.weltpixel-quickview):after{content:"";display:block;background-color:#000;opacity:0;width:100%;height:100%;position:absolute;left:0;top:0;z-index:2;visibility:hidden;transition:all .3s}
.products-grid .product-item .product-item-info:hover .product-item-photo > a:not(.weltpixel-quickview):after{opacity:.1;visibility:visible}
.page-header.type2.header-newskin.sticky-header .minicart-wrapper .block-minicart:after{right:38px}
.page-header.type2.header-newskin.sticky-header .minicart-wrapper .block-minicart:before{right:39px}
.swatch-attribute.size .swatch-option,.swatch-attribute.manufacturer .swatch-option{background:#fff;color:#636363;border-color:#e9e9e9}
.swatch-option.text{min-width:26px;line-height:18px;padding:3px;height:26px}
.pages a.page,.pages strong.page,.pages .action{background:transparent;color:#706f6c;font-size:15px;font-weight:600;line-height:30px}
.pages a.page:hover,.pages a.action:hover{background-color:transparent;color:#706f6c}
.pages a.action:hover:before{color:#706f6c!important}
.pages .action{border-color:transparent}
.product-info-main .product-info-stock-sku{color:#333;font-size:14px;padding-bottom:23px}
.product-reviews-summary .reviews-actions a{color:#21293c}
.product-info-main .product-info-stock-sku{color:#21293c}
.catalog-product-view .sidebar .custom-block.custom-block-1{margin-top:-25px}
.block-minicart .block-content > .actions > .secondary .action.viewcart{color:#333;font-weight:500;font-family:"Oswald"}
.cms-index-index .single-images{margin-bottom:5px}
.product-item .rating-summary .rating-result > span:before{color:#575f68}
@media (max-width: 767px) {
.block-category-list .block-title,.block-category-list .block-content,.sidebar-title,.sidebar .custom-block,.sidebar-filterproducts.custom-block + h2,.sidebar-filterproducts.custom-block + h2 +h5,.sidebar-filterproducts.custom-block + h2 + h5 + p{padding-left:0;padding-right:0}
}
.page-header .switcher .options .action.toggle{color:#bde1f5}
.page-wrapper > .breadcrumbs{margin-bottom:0}
.products-grid .product-item-details .product-item-actions .tocart{text-transform:uppercase;font-size:12.53px;font-family:"Oswald";font-weight:400;letter-spacing:.025em;color:#fff;line-height:30px;background-color:#08c;border-color:#08c;}
.products-grid .product-item-details .product-item-actions .tocart:hover{background-color:#006496!important;border-color:#006496!important;color:#fff}
.product-item .tocart:before{content:"\e87f";font-size:17px;vertical-align:middle}
.product-social-links .action.towishlist:before,.product-addto-links .action.towishlist:before,.block-bundle-summary .action.towishlist:before,.product-item .action.towishlist:before,.table-comparison .action.towishlist:before{content:"\e889"}'
                ),
                array(
                    'version' => '0.0.6',
                    'path' => 'persistent/options/lifetime',
                    'value' => '14400'
                ),
        );
    }

   
    /*
     * Note: heredoc notation <<<HTML / HTML must be the last array element to work properly
     *
     * @return Array with list od cms blocks
     */
    protected function _getBlockArray()
    {
        return array(
          
            /*
             * Minicart section
             */
            array(
                'version' => '0.0.1',
                'identifier' => 'header_contact_information',
                'stores' => [0],
                'title' => 'Header Contact Information',
                'content' => <<<HTML
<div id="phone-hrs">
    <a href="https://goo.gl/maps/seJQCD3gzao" target="_blank">Visit Us</a> or Call Us: <a id="phone-number" href="tel:+18009387925">(800) 938-7925</a> &nbsp;&nbsp; Today's Hours: 7:00am - 6:00pm
</div>
HTML
            ),
             array(
                'version' => '0.0.3',
                'identifier' => 'home_sidebar',
                'stores' => [0],
                'title' => 'Home Sidebar',
                'content' => <<<HTML
<div class="sidebar">
    <div class="home-side-menu">
      <h2 class="side-menu-title">CATEGORIES</h2>
      {{block class="Smartwave\Megamenu\Block\Topmenu" name="sw.sidenav" template="Smartwave_Megamenu::sidemenu.phtml" ttl="3600"}} </div>
    <!-- start -->
    <div id="ads-slider-demo-9">
      <div class="item">
        <h2><a href="#">Blog Articles</a></h2>
        <ul class="recent-blog" style="list-style:none; padding-inline-start:12px; padding-right:12px;">
          <li><a title="Bosch Laser Level - GLL3-330CG" href="https://www.masterwholesale.com/blog/bosch-green-line-laser-level-review/" target="_blank">Review: Bosch Green Line Laser Level</a></li>
          <li><a title="Makita LXT Lithium Ion Cordless Brushless Angle Grinder" href="https://www.masterwholesale.com/blog/makita-18v-lxt-lithium-ion-cordless-brushless-angle-grinder-review/" target="_blank">Review: Makita&nbsp;<span>LXT Cordless Brushless Angle Grinder&nbsp;</span></a></li>
          <li><a title="Mixing Laticrete PermaColor Grout" href="https://www.masterwholesale.com/blog/how-to-mix-small-batch-laticrete-permacolor-select-grout/" target="_blank">How to Mix a Small Batch of Laticrete PermaColor Grout</a></li>
          <li><a title="Polishing Tile Edges with Diamond Hand Polishing Pads" href="https://www.masterwholesale.com/blog/polish-stone-porcelain-tile-diamond-hand-polishing-pads/" target="_blank">How to Polish Tile Edges w/ Diamond Hand Polishing Pads</a></li>
          <li><a title="Stoning Porcelain and Stone Tile" href="https://www.masterwholesale.com/blog/stone-edge-porcelain-tile-stone/" target="_blank">How to Stone or Edge Porcelain and Stone Tiles</a></li>
          <li><a title="Bridge Saw vs Sliding Table Saw" href="https://www.masterwholesale.com/blog/366-2/" target="_blank">Bridge Saw vs Sliding Table Saw</a></li>
          <li><a title="How to Core with a Diamond Core Drill Bit" href="https://www.masterwholesale.com/blog/dry-core-drill-tile-stone/" target="_blank">How to Dry Core Drill on Stone or Tile</a></li>
          <li><a title="How to Reopen a diamond Core Bit " href="https://www.masterwholesale.com/blog/master-wholesale-reopen-diamond-core-bit/" target="_blank">How to Sharpen or Reopen a Diamond Core Bit</a></li>
          <li><a title="Resin Glass Blade Shootout" href="https://www.masterwholesale.com/blog/resin-glass-blade-shootout/" target="_blank">Resin Glass Blade Shootout with Blake Adsero</a></li>
          <li><a title="Makita Polishing Kit" href="https://www.masterwholesale.com/blog/makita-pw5001c-wet-polishing-kit-master-wholesale/" target="_blank">How to Polish an Exposed Edge on Stone Tile</a></li>
          <li><a title="Deluxe Dry Polishing Kit" href="https://www.masterwholesale.com/blog/?p=254" target="_self">MWI Deluxe Dry Polishing Kit Demo</a></li>
          <li><a title="Laser Level Shootout" href="https://www.masterwholesale.com/blog/laser-level-shootout/" target="_self">Laser Level Shootout</a></li>
          <li><a title="Ishii Tile Cutters" href="https://www.masterwholesale.com/blog/how-to-assemble-and-use-the-ishii-blue-tile-cutters/" target="_self">How to Assemble and Use Ishii BlueTile Cutters</a></li>
          <li><a title="Diamond Blade Shootout" href="https://www.masterwholesale.com/blog/tile-saw-diamond-blade-shootout/" target="_self">Tile Saw Diamond Blade Shootout</a></li>
        </ul>
      </div>
    </div>
    <!-- end --> 
    <!-- start -->
    <div id="ads-slider-demo-9" class="owl-carousel">
      <div class="item" style="text-align:center;"> <img src="" alt="" style="display:inline-block;"/> </div>
    </div>
    <!-- end --> 
    <!-- start -->
    <div id="ads-slider-demo-9">
      <div class="item" style="text-align:center;"> {{block class="Magento\Framework\View\Element\Template" name="single_special" template="Magento_Catalog::product/view/single_special.phtml" ttl="3600"}} </div>
    </div>
    <!-- end --> 
  </div>
HTML
            ),
        );
    }


     /*
     * Note: heredoc notation <<<HTML / HTML must be the last array element to work properly
     *
     * @return Array with list od cms pages
     */
    protected function _getPageArray()
    {
        return array(

            array(
                'version' => '0.0.2',
                'identifier' => 'porto_home_6',
                'page_layout' => '1column',
                'stores' => [0],
                'title' => 'Tile, Stone, Concrete Tools & Supply - Master Wholesale',
                'content_heading' => 'Tile, Stone, Concrete Tools & Supply - Master Wholesale',
                'content' => <<<HTML
<div class="row" style="margin: 0 -10px;">
  <div class="col-lg-3" style="padding: 0 10px;">
    <div class="home-side-menu">
      <h2 class="side-menu-title">CATEGORIES</h2>
      {{block class="Smartwave\Megamenu\Block\Topmenu" name="sw.sidenav" template="Smartwave_Megamenu::sidemenu.phtml" ttl="3600"}}
    </div>
  </div>
  <div class="col-lg-9" style="padding: 0 10px;">
    <div id="banner-slider-demo-9" class="owl-carousel owl-banner-carousel owl-bottom-narrow" style="margin-bottom:20px;">
      <div class="item">
        <div style="position:relative">
          <img src="{{media url=&quot;wysiwyg/Master/mh-slide.png&quot;}}" alt="" />
        </div>
      </div>
      <div class="item">
        <div style="position:relative">
          <img src="{{media url=&quot;wysiwyg/Master/leveling-slide.jpg&quot;}}" alt="" />
        </div>
      </div>
      <div class="item">
        <div style="position:relative">
          <img src="{{media url=&quot;wysiwyg/schluter-slide.jpg&quot;}}" alt="" />
        </div>
      </div>
    </div>
    <script type="text/javascript">
      require([
        'jquery',
        'owl.carousel/owl.carousel.min'
      ], function ($) {
        $("#banner-slider-demo-9").owlCarousel({
          items: 1,
          autoplay: true,
          autoplayTimeout: 5000,
          autoplayHoverPause: true,
          dots: true,
          nav: false,
          navRewind: true,
          animateIn: 'fadeIn',
          animateOut: 'fadeOut',
          loop: true
        });
      });
    </script>
  </div>
</div>
<div class="row" style="margin: 0 -10px;">



  <div class="col-lg-3" style="padding: 0 10px;">
   <!-- start -->
    <div id="ads-slider-demo-9">
      <div class="item" style="text-align:center;">
        <br />
<br />
<br />
<br />
<br />
<br />
<br />
      </div>
    </div> 
   <!-- end -->
<br /><br />
<!-- start -->
    <div id="ads-slider-demo-9">
      <div class="item">
             <h2><a href="#">Blog Articles</a></h2>
<ul class="recent-blog" style="list-style:none; padding-inline-start:12px; padding-right:12px;">
<li><a title="Bosch Laser Level - GLL3-330CG" href="https://www.masterwholesale.com/blog/bosch-green-line-laser-level-review/" target="_blank">Review: Bosch Green Line Laser Level</a></li>
<li><a title="Makita LXT Lithium Ion Cordless Brushless Angle Grinder" href="https://www.masterwholesale.com/blog/makita-18v-lxt-lithium-ion-cordless-brushless-angle-grinder-review/" target="_blank">Review: Makita&nbsp;<span>LXT Cordless Brushless Angle Grinder&nbsp;</span></a></li>
<li><a title="Mixing Laticrete PermaColor Grout" href="https://www.masterwholesale.com/blog/how-to-mix-small-batch-laticrete-permacolor-select-grout/" target="_blank">How to Mix a Small Batch of Laticrete PermaColor Grout</a></li>
<li><a title="Polishing Tile Edges with Diamond Hand Polishing Pads" href="https://www.masterwholesale.com/blog/polish-stone-porcelain-tile-diamond-hand-polishing-pads/" target="_blank">How to Polish Tile Edges w/ Diamond Hand Polishing Pads</a></li>
<li><a title="Stoning Porcelain and Stone Tile" href="https://www.masterwholesale.com/blog/stone-edge-porcelain-tile-stone/" target="_blank">How to Stone or Edge Porcelain and Stone Tiles</a></li>
<li><a title="Bridge Saw vs Sliding Table Saw" href="https://www.masterwholesale.com/blog/366-2/" target="_blank">Bridge Saw vs Sliding Table Saw</a></li>
<li><a title="How to Core with a Diamond Core Drill Bit" href="https://www.masterwholesale.com/blog/dry-core-drill-tile-stone/" target="_blank">How to Dry Core Drill on Stone or Tile</a></li>
<li><a title="How to Reopen a diamond Core Bit " href="https://www.masterwholesale.com/blog/master-wholesale-reopen-diamond-core-bit/" target="_blank">How to Sharpen or Reopen a Diamond Core Bit</a></li>
<li><a title="Resin Glass Blade Shootout" href="https://www.masterwholesale.com/blog/resin-glass-blade-shootout/" target="_blank">Resin Glass Blade Shootout with Blake Adsero</a></li>
<li><a title="Makita Polishing Kit" href="https://www.masterwholesale.com/blog/makita-pw5001c-wet-polishing-kit-master-wholesale/" target="_blank">How to Polish an Exposed Edge on Stone Tile</a></li>
<li><a title="Deluxe Dry Polishing Kit" href="https://www.masterwholesale.com/blog/?p=254" target="_self">MWI Deluxe Dry Polishing Kit Demo</a></li>
<li><a title="Laser Level Shootout" href="https://www.masterwholesale.com/blog/laser-level-shootout/" target="_self">Laser Level Shootout</a></li>
<li><a title="Ishii Tile Cutters" href="https://www.masterwholesale.com/blog/how-to-assemble-and-use-the-ishii-blue-tile-cutters/" target="_self">How to Assemble and Use Ishii BlueTile Cutters</a></li>
<li><a title="Diamond Blade Shootout" href="https://www.masterwholesale.com/blog/tile-saw-diamond-blade-shootout/" target="_self">Tile Saw Diamond Blade Shootout</a></li>
</ul>
      </div>
    </div> 
   <!-- end -->
    <div id="ads-slider-demo-9" class="owl-carousel">
      <div class="item" style="text-align:center;">
        <img src="" alt="" style="display:inline-block;"/>
      </div>
    </div>
       <!-- start -->
    <div id="ads-slider-demo-9">
      <div class="item" style="text-align:center;">
       {{block class="Magento\Framework\View\Element\Template" name="single_special" template="Magento_Catalog::product/view/single_special.phtml" ttl="3600"}}
      </div>
    </div> 
   <!-- end -->
  </div>
  <div class="col-lg-9" style="padding: 0 10px;">
    <h2 class="filterproduct-title"><span class="content"><strong>FEATURED ITEMS</strong></span></h2>
    <div id="featured_product" class="owl-top-narrow">
      {{block class="Smartwave\Filterproducts\Block\Home\FeaturedList" name="featured_product" product_count="8" aspect_ratio="0" image_width="140" product_type="1" template="grid.phtml"}}
    </div>
    <h2 class="filterproduct-title"><span class="content"><strong>BESTSELLERS</strong></span></h2>
    <div id="new_product" class="owl-top-narrow">
       {{block class="Smartwave\Filterproducts\Block\Home\BestsellersList" name="bestseller_list" product_count="8" aspect_ratio="0" product_type="1" image_width="140" template="grid.phtml"}}
    </div>
  </div>
<style type="text/css">
  #banner-slider-demo-9 img {width:100%;}
  #banner-slider-demo-9 .content h2 {
    font-size: 44px;
    font-weight: 900;
    letter-spacing: -0.025em;
    text-transform: uppercase;
    line-height: 38px;
    margin-bottom: 10px;
    margin-top:0;
  }
  #banner-slider-demo-9 .content span {
    font-size:18px;
    line-height:38px;
    font-weight: 700;
    text-transform:uppercase;
  }
  #banner-slider-demo-9 .content p {
    font-size:14px;
    font-weight:300;
    margin-bottom:10;
  }
  #banner-slider-demo-9 .content .btn-default {
    font-size: 14px;
    line-height: 25px;
    letter-spacing: 0.025em;
    padding: 10px 34px;
    border-radius:3px;
    background-color: #010204;
    color: #fff;
    font-family: 'Oswald';
    text-transform: uppercase; 
    margin-top: 28px;
  } 
  #testimonials-slider-demo-9 {
    border: solid 2px #0188cc;
    border-radius: 2px;
  }
  @media (min-width: 768px) {
    .page-header .nav-sections {
      display: none;
    }
    h2.side-menu-title {
      padding: 16px 25px;
    }
.sw-megamenu.navigation.side-megamenu li.level0 > a {
    margin: 0 20px;
    line-height: 44px;
    font-weight: 600;
}
  }
  @media (max-width: 767px) {
    #banner-slider-demo-9 img {width:100%;height:100%;}
    #banner-slider-demo-9 .owl-controls {
      display:none !important;
    }
    #banner-slider-demo-9 .content {
      top:15% !important;
    }
    #banner-slider-demo-9 .content h2 {
      font-size: 24px; 
      line-height: 1;
      margin-bottom: 5px;
    }
    #banner-slider-demo-9 .content span {
      font-size: 11px !important;
      line-height: 23px; 
    }
    #banner-slider-demo-9 .content span b {
      font-size: 15px !important;
    }
    #banner-slider-demo-9 .content p {
      font-size: 11px !important; 
      margin-bottom: 10px;
    }
    #banner-slider-demo-9 .content .btn-default {
      font-size: 10px !important;
      line-height: 20px; 
      padding: 3px 13px;
      border-radius: 3px; 
      margin-top: 0px;
    } 
  }
</style>
HTML
            ),
            array(
                'version' => '0.0.3',
                'identifier' => 'porto_home_6',
                'page_layout' => '1column',
                'stores' => [0],
                'title' => 'Tile, Stone, Concrete Tools & Supply - Master Wholesale',
                'content_heading' => 'Tile, Stone, Concrete Tools & Supply - Master Wholesale',
                'content' => <<<HTML
<div class="row" style="margin: 0 -10px;">
  <div class="col-lg-3" style="padding: 0 10px;">
        {{block class="Magento\\Cms\\Block\\Block" block_id="home_sidebar"}}
  </div>
  <div class="col-lg-9" style="padding: 0 10px;">
    <div id="banner-slider-demo-9" class="owl-carousel owl-banner-carousel owl-bottom-narrow" style="margin-bottom:20px;">
      <div class="item">
        <div style="position:relative"> <img src="{{media url=&quot;wysiwyg/Master/mh-slide.png&quot;}}" alt="" /> </div>
      </div>
      <div class="item">
        <div style="position:relative"> <img src="{{media url=&quot;wysiwyg/Master/leveling-slide.jpg&quot;}}" alt="" /> </div>
      </div>
      <div class="item">
        <div style="position:relative"> <img src="{{media url=&quot;wysiwyg/schluter-slide.jpg&quot;}}" alt="" /> </div>
      </div>
    </div>
    <script type="text/javascript">
      require([
        'jquery',
        'owl.carousel/owl.carousel.min'
      ], function ($) {
        $("#banner-slider-demo-9").owlCarousel({
          items: 1,
          autoplay: true,
          autoplayTimeout: 5000,
          autoplayHoverPause: true,
          dots: true,
          nav: false,
          navRewind: true,
          animateIn: 'fadeIn',
          animateOut: 'fadeOut',
          loop: true
        });
      });
    </script>
    <h2 class="filterproduct-title"><span class="content"><strong>FEATURED ITEMS</strong></span></h2>
    <div id="featured_product" class="owl-top-narrow"> {{block class="Smartwave\Filterproducts\Block\Home\FeaturedList" name="featured_product" product_count="8" aspect_ratio="0" image_width="140" product_type="1" template="grid.phtml"}} </div>
    <h2 class="filterproduct-title"><span class="content"><strong>BESTSELLERS</strong></span></h2>
    <div id="new_product" class="owl-top-narrow"> {{block class="Smartwave\Filterproducts\Block\Home\BestsellersList" name="bestseller_list" product_count="8" aspect_ratio="0" product_type="1" image_width="140" template="grid.phtml"}} </div>
  </div>
</div>
<style type="text/css">
  #banner-slider-demo-9 img {width:100%;}
  #banner-slider-demo-9 .content h2 {
    font-size: 44px;
    font-weight: 900;
    letter-spacing: -0.025em;
    text-transform: uppercase;
    line-height: 38px;
    margin-bottom: 10px;
    margin-top:0;
  }
  #banner-slider-demo-9 .content span {
    font-size:18px;
    line-height:38px;
    font-weight: 700;
    text-transform:uppercase;
  }
  #banner-slider-demo-9 .content p {
    font-size:14px;
    font-weight:300;
    margin-bottom:10;
  }
  #banner-slider-demo-9 .content .btn-default {
    font-size: 14px;
    line-height: 25px;
    letter-spacing: 0.025em;
    padding: 10px 34px;
    border-radius:3px;
    background-color: #010204;
    color: #fff;
    font-family: 'Oswald';
    text-transform: uppercase; 
    margin-top: 28px;
  } 
  #testimonials-slider-demo-9 {
    border: solid 2px #0188cc;
    border-radius: 2px;
  }
  @media (min-width: 768px) {
    .page-header .nav-sections {
      display: none;
    }
    h2.side-menu-title {
      padding: 16px 25px;
    }
.sw-megamenu.navigation.side-megamenu li.level0 > a {
    margin: 0 20px;
    line-height: 44px;
    font-weight: 600;
}
  }
  @media (max-width: 767px) {
    #banner-slider-demo-9 img {width:100%;height:100%;}
    #banner-slider-demo-9 .owl-controls {
      display:none !important;
    }
    #banner-slider-demo-9 .content {
      top:15% !important;
    }
    #banner-slider-demo-9 .content h2 {
      font-size: 24px; 
      line-height: 1;
      margin-bottom: 5px;
    }
    #banner-slider-demo-9 .content span {
      font-size: 11px !important;
      line-height: 23px; 
    }
    #banner-slider-demo-9 .content span b {
      font-size: 15px !important;
    }
    #banner-slider-demo-9 .content p {
      font-size: 11px !important; 
      margin-bottom: 10px;
    }
    #banner-slider-demo-9 .content .btn-default {
      font-size: 10px !important;
      line-height: 20px; 
      padding: 3px 13px;
      border-radius: 3px; 
      margin-top: 0px;
    } 
  }
</style>
HTML
            ),

        );
    }


}
