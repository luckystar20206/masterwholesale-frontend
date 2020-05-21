<?php
namespace Smartwave\Dailydeals\Model\Dailydeal\Source;

class SwDealProduct implements \Magento\Framework\Option\ArrayInterface
{
    const FIXED = 1;
    const PERCENTAGE = 2;

    /**
     * to option array
     *
     * @return array
     */
    protected $productFactory;
    
    

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
    
        $this->productFactory=$productFactory;
    }


    public function toOptionArray()
    {

        $childArray=[];

        $productcollection=$this->productFactory->create()->getCollection();
        $productcollection->addAttributeToSelect('*');

        foreach ($productcollection as $_product) {
            if ($_product->getTypeId() == "bundle") {
                $product = $this->productFactory->create()->load($_product->getId());
                //get all the selection products used in bundle product.
                $selectionCollection = $product->getTypeInstance(true)
                    ->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product),
                        $product
                    );
               
                
                foreach ($selectionCollection as $proselection) {
                    array_push($childArray, $proselection->getProductId());
                }
            }
        }
        

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currencySymbol=$objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $currencysymbol=$currencySymbol->getStore()->getCurrentCurrency()->getCurrencySymbol();
        
        $productcollection=$this->productFactory->create()->getCollection();

        $productcollection->addAttributeToSelect('*');
        $productcollection->addAttributeToFilter('entity_id', ['nin'=>$childArray]);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $options = ['value'=>'','label'=>'-- Select Product --'];
        foreach ($productcollection as $product) {
            $productId = $product->getId(); //this is child product id
                    
            $getproduct = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($productId);

            if (isset($getproduct[0])) {
                $productcollection1=$this->productFactory->create()->getCollection();

                 $productcollection1->addFieldToSelect('*');

                 $productcollection1->addFieldToFilter('entity_id', ['eq'=>$getproduct[0]]);
              
                $sku=$productcollection1->getFirstItem()->getSku();
                $name=$productcollection1->getFirstItem()->getName() ;
                $price=$product->getFinalPrice();
                $id=$productcollection1->getFirstItem()->getId();
            } else {
                if ($product->getTypeId() == "bundle") {
                    $bundleprice=[];

                    $sku=$product->getSku();
                    $name=$product->getName();
                    $id=$product->getId();
                   

                    $bundleproduct = $this->productFactory->create()->load($product->getId());
                    //get all the selection products used in bundle product.
                    $selectionCollection = $bundleproduct->getTypeInstance(true)
                        ->getSelectionsCollection(
                            $bundleproduct->getTypeInstance(true)->getOptionsIds($bundleproduct),
                            $bundleproduct
                        );
                   
                    
                    foreach ($selectionCollection as $proselection) {
                        array_push($bundleprice, $proselection->getFinalPrice());
                    }

                    $price=min($bundleprice);
                } elseif ($product->getTypeId() == "grouped") {
                    $groupedprice=[];

                     $groupedproduct = $this->productFactory->create()->load($product->getId());
                     $associatedProducts =$groupedproduct->getTypeInstance()->getAssociatedProducts($groupedproduct);
                    foreach ($associatedProducts as $_item) {
                        array_push($groupedprice, $_item->getFinalPrice());
                    }
                          

                    $sku=$product->getSku();
                    $name=$product->getName();
                    $id=$product->getId();
                    $price=min($groupedprice);
                } elseif ($product->getvisibility() !=1) {
                    $sku=$product->getSku();
                    $name=$product->getName();
                    $id=$product->getId();
                    $price=$product->getFinalPrice();
                }
            }

            
            if ($price != 0) {
                $options[] =
                [ 'value'=>$sku,
                'label'=>"ID:".$id."  ".$name."- ".$currencysymbol."".round($price, 2)." "
                ];
            }
        }

        $unique = array_map("unserialize", array_unique(array_map("serialize", $options)));

        return $unique;
    }
}
