<?php
namespace Smartwave\Dailydeals\Block;

//use Magento\Framework\View\Element\Template;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class Main extends \Magento\Catalog\Block\Product\ListProduct
{


    protected $productFactory;
    protected $dailydealFactory;

    protected $scopeConfig;
        
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory,
        array $data = []
    ) {
    
        $this->productFactory= $productFactory;
        $this->dailydealFactory=$dailydealFactory;
        
        $this->scopeConfig=$context->getScopeConfig();
             
        return parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    // @return Productcollection whose status is enabled
    public function getDailydealEnableProduct()
    {
        $collection=$this->getDailydealCollection();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('sw_deal_enable', ['eq' => 1]);

        return $collection;
    }

    public function getDailydealCollection()
    {
        $collection=$this->dailydealFactory->create()->getCollection();
        return $collection;
    }

    // Get Product Data which is common in DailydealCollection
    public function getDailyDealProduct($productSku)
    {
        $productCollection=$this->productFactory->create()->getCollection();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addAttributeToFilter('sku', ['eq'=>$productSku]);
        
        return $productCollection;
    }

    //Retrun Recently dailydeal offer Collection ( duration is 2 days before expired and 2 days ago comming soon offer)
    public function recentlyDailydeal($productSku)
    {
       
        $dailydealcollection=$this->getDailydealCollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$productSku]);
        
        if ($dailydealcollection->getSize() ==1) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        
            $curdate=strtotime($objDate->gmtDate("Y-m-d H:i:s"));
            $Todate=strtotime($dailydealcollection->getFirstItem()->getSwDateTo());
            $fromdate=strtotime($dailydealcollection->getFirstItem()->getSwDateFrom());
            
            // calculate two days time
            $twodays_duration=172800;
            
            $expiredduration=$curdate-$Todate; // It returns positive value
                
            $comingsoonduration=$curdate-$fromdate; // It returns Nagative value
             
            // Check datetime duration before two days and ago twodays
            if ($expiredduration > $twodays_duration || $comingsoonduration < -$twodays_duration) {
                return false;
            } else {
                return true; // Return True if collection of product which are expired and comming Duration is two days
            }
        } else {
            return false;
        }
    }
}
