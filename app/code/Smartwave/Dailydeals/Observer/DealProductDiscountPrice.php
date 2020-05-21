<?php
namespace Smartwave\Dailydeals\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
 
class DealProductDiscountPrice implements ObserverInterface
{
    protected $dailydealFactory;
    protected $helper;
    protected $scopeConfig;

    public function __construct(
        \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory,
        \Smartwave\Dailydeals\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->dailydealFactory = $dailydealFactory;
        $this->scopeConfig=$scopeConfig;
        $this->helper=$helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');

        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        
        $cartproduct_id=$item->getProductId();
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       
       
        // Check For Grouped Product
        $product = $objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped')->getParentIdsByChild($item->getProduct()->getId());
        if (isset($product[0])) {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product[0]);
            $groupedProductFalg=1;
            $groupedProduct=$product->getId();
        } else {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());
        }
       
        $dailydealcollection=$this->dailydealFactory->create()->getCollection();
        $dailydealcollection->addFieldToSelect('*');
        $dailydealcollection->addFieldToFilter('sw_product_sku', ['eq' => $product->getSku()]);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objDate =$objectManager ->create('Magento\Framework\Stdlib\DateTime\DateTime');
       
        $curdate=strtotime($objDate->gmtDate("Y-m-d H:i:s"));
        $Todate=strtotime($dailydealcollection->getFirstItem()->getSwDateTo());
        $fromdate=strtotime($dailydealcollection->getFirstItem()->getSwDateFrom());
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $configPath = "sw_dailydeal/general/dailydeal_enabled";

        $enabledvalue = $this->scopeConfig->getValue($configPath, $storeScope);
        
        if ($enabledvalue==1 && $curdate <= $Todate && $curdate >= $fromdate) {
            if ($item->getProduct()->getTypeId() == "bundle") {
                $bundleItemPrice=[];
                $options=$item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                
                foreach ($options['bundle_options'] as $bundleitems) {
                    foreach ($bundleitems['value'] as $sub) {
                        array_push($bundleItemPrice, $sub['price']);
                    }
                }

                foreach ($item->getQuote()->getAllItems() as $bundleitems) {
                        /** @var $bundleitems\Magento\Quote\Model\Quote\Item */
                    if (max($bundleItemPrice) == $bundleitems->getProduct()->getPrice()) {
                        if ($dailydealcollection->getFirstItem()->getSwDiscountType() == 1) {
                            $finalprice=$bundleitems->getProduct()->getFinalPrice()-$dailydealcollection->getFirstItem()->getSwDiscountAmount();
                        } elseif ($dailydealcollection->getFirstItem()->getSwDiscountType() == 2) {
                            $finalprice=$bundleitems->getProduct()->getFinalPrice()-(($item->getProduct()->getFinalPrice()*$dailydealcollection->getFirstItem()->getSwDiscountAmount())/100);
                        }
                                
                        $bundleitems->setCustomPrice($finalprice);
                        $bundleitems->setOriginalCustomPrice($finalprice);
                        $item->getProduct()->setIsSuperMode(true);

                        break;
                    }
                }
                    

                       $item->getProduct()->setIsSuperMode(true);
            } else {
                if (isset($groupedProductFalg)) {
                    $groupedItemPrice=[];
                    foreach ($item->getQuote()->getAllItems() as $groupedItem) {
                        $grouped_product = $objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped')->getParentIdsByChild($groupedItem->getProduct()->getId());
                        if (isset($grouped_product[0])) {
                            if ($groupedProduct==$grouped_product[0]) {
                                array_push($groupedItemPrice, $groupedItem->getProduct()->getPrice());
                            }
                        }
                    }

                    foreach ($item->getQuote()->getAllItems() as $groupedItem) {
                        if (min($groupedItemPrice) == $groupedItem->getProduct()->getPrice()) {
                            if ($dailydealcollection->getFirstItem()->getSwDiscountType() == 1) {
                                $price=$groupedItem->getProduct()->getPrice()-$dailydealcollection->getFirstItem()->getSwDiscountAmount();
                            } elseif ($dailydealcollection->getFirstItem()->getSwDiscountType() == 2) {
                                $price=$groupedItem->getProduct()->getPrice()-(($groupedItem->getProduct()->getPrice()*$dailydealcollection->getFirstItem()->getSwDiscountAmount())/100);
                            }
                            break;
                        }
                    }
                } else {
                    $price = $dailydealcollection->getFirstItem()->getSwProductPrice();
                }
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
    }
}
