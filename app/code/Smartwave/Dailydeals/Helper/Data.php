<?php
namespace Smartwave\Dailydeals\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $dailydealFactory;
    protected $scopeConfig;
    protected $productFactory;
    
    public function __construct(
        \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
    
        $this->dailydealFactory = $dailydealFactory;
        $this->scopeConfig=$scopeConfig;
        $this->productFactory= $productFactory;
    }

    public function chkEnableDailydeals()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
       
        $configPath = "sw_dailydeal/general/dailydeal_enabled";
       
        $chkEnableDailydeals = $this->scopeConfig->getValue($configPath, $storeScope);
        
        return $chkEnableDailydeals;
    }

    // Get ObjectManager Instance
    public function getObjectManagerInstance()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
    
    //Check Dailydeal Product
    public function isDealProduct($productId)
    {
        if(!$this->chkEnableDailydeals())
            return false;
        $productcollection=$this->productFactory->create()->getCollection();
        $productcollection->addAttributeToSelect('*');
        $productcollection->addAttributeToFilter('entity_id', ['eq'=>$productId]);
        $sku=$productcollection->getFirstItem()->getSku();
        
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$sku]);
        
        if ($dailydealcollection->getSize() ==1) {
            $objDate = $this->getObjectManagerInstance()->create('Magento\Framework\Stdlib\DateTime\DateTime');
        
            $curdate=strtotime($this->getcurrentDate());
            $Todate=strtotime($this->getDailydealToDate($sku));
            $fromdate=strtotime($this->getDailydealFromDate($sku));
            
            if (( $curdate <= $Todate ) && ($curdate >= $fromdate)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // Get Product Price
    public function getProductPrice($sku)
    {
        $productcollection=$this->productFactory->create()->getCollection();
        $productcollection->addAttributeToSelect('*');
        $productcollection->addAttributeToFilter('sku', ['eq'=>$sku]);
        $productcollection->addAttributeToFilter('type_id', ['neq'=>'bundle']);
        if ($productcollection->getSize() ==1 && $productcollection->getFirstItem()->getTypeId() !="grouped") {
            return $productcollection->getFirstItem()->getFinalPrice();
        } else {
            return 1;
        }
    }
    // Get Bundle Discount Value
    public function getbundleProductDiscount($sku)
    {
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$sku]);

        if ($dailydealcollection->getFirstItem()->getSwDiscountType() ==1) {
            return '<div style=" margin-top:20px; "><strong>Save:'.$this->getcurrencySymbol().''.number_format($dailydealcollection->getFirstItem()->getSwDiscountAmount(), 2).'</strong></div>';
        } elseif ($dailydealcollection->getFirstItem()->getSwDiscountType() ==2) {
            return '<div style="margin-top:20px;"><strong>OFF:'.number_format($dailydealcollection->getFirstItem()->getSwDiscountAmount(), 2).'%</strong></div>';
        }
    }
    //Get "Product price" by ProductId
    public function getDealproductbyId($productId)
    {
        $productcollection=$this->productFactory->create()->getCollection();
        $productcollection->addAttributeToSelect('*');
        $productcollection->addAttributeToFilter('entity_id', ['eq'=>$productId]);
        $sku=$productcollection->getFirstItem()->getSku();
        
        return $this->getDealProductPrice($sku);
    }
    
    // Get Current Currency Symbol
    public function getcurrencySymbol()
    {
        $currencySymbol=$this->getObjectManagerInstance()->create('Magento\Store\Model\StoreManagerInterface');
        return $currencySymbol->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    // Get Current Date
    public function getcurrentDate()
    {
         $objDate = $this->getObjectManagerInstance()->create('Magento\Framework\Stdlib\DateTime\DateTime');
         return $objDate->gmtDate("Y-m-d H:i:s");
    }

    // Get Collection of dailydeal
   
    public function getDailydealcollection()
    {
        $dailydealcollection=$this->dailydealFactory->create()->getCollection();
        return $dailydealcollection;
    }
    
    // Get Discount Value  of Dailydeal Product
    public function getDealProductDiscountValue($dealproductsku)
    {
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$dealproductsku]);
        
        return $dailydealcollection->getFirstItem()->getSwDiscountAmount();
    }
    
    // Get Dailydeal Product with Discount Price
    public function getDealProductPrice($dealproductsku)
    {
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$dealproductsku]);
        
        return $dailydealcollection->getFirstItem()->getSwProductPrice();
    }
    
    // Get Dailydeal Product TO date
    public function getDailydealToDate($dealproductsku)
    {
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$dealproductsku]);
        
        return $dailydealcollection->getFirstItem()->getSwDateTo();
    }
    // Get Dailydeal Product FROM Date
    public function getDailydealFromDate($dealproductsku)
    {
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$dealproductsku]);
        
        return $dailydealcollection->getFirstItem()->getSwDateFrom();
    }
            
    // Get "OFF value" (in percentage) of Dailydeal Product
    public function getDealOffValue($dealproductsku)
    {
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$dealproductsku]);
        
        $discountType=$dailydealcollection->getFirstItem()->getSwDiscountType();
        if ($discountType ==1) {
            $off=(($this->getProductPrice($dealproductsku)-$this->getDealProductPrice($dealproductsku))* 100)/  $this->getProductPrice($dealproductsku) ;
            return $off;
        } elseif ($discountType ==2) {
            return $dailydealcollection->getFirstItem()->getSwDiscountAmount();
        }
    }
    
    // Get "Save value" (In price) of dailydeal Product
    public function getDealSaveValue($dealproductsku)
    {
        $dailydealcollection=$this->getDailydealcollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq'=>$dealproductsku]);
        
        $discountType=$dailydealcollection->getFirstItem()->getSwDiscountType();
        if ($discountType ==1) {
            return $dailydealcollection->getFirstItem()->getSwDiscountAmount();
        } elseif ($discountType ==2) {
            $save=$this->getProductPrice($dealproductsku) - $this->getDealProductPrice($dealproductsku);
            return $save;
        }
    }
}
