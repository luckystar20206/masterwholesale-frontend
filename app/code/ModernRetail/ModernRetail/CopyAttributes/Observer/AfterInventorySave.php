<?php
namespace ModernRetail\CopyAttributes\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class AfterInventorySave  implements ObserverInterface{



    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \ModernRetail\CopyAttributes\Model\Resource\Copy $copy,
        \ModernRetail\CopyAttributes\Helper\Data $helper,
        \Magento\Catalog\Model\Product $productModel,
//        \Magento\CatalogInventory\Model\Stock\Item $stockItem,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->configurableProduct = $configurableProduct;
        $this->storeManager = $storeManager;
        $this->productAction = $productAction;
        $this->copy = $copy;
        $this->helper = $helper;
        $this->productModel = $productModel;
//        $this->stockItem = $stockItem;
        $this->resource = $resource;


    }

    public function execute(\Magento\Framework\Event\Observer $observer){



        //d(array_keys($observer->getData()));

        //$item = $observer->getDataObject();

        $_product = $observer->getDataObject();
        //if ($_product)
            //$_product = $_product->load($_product->getId());

        /**
         * If this is child product
         */
        if (!$_product) return false;
        $parentIds =  $this->configurableProduct->getParentIdsByChild($_product->getId());

        if ($parentIds){
            foreach($parentIds as $configurable_id){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $configurableProductObject = $objectManager->create('Magento\Catalog\Model\Product');
                $configurableProductObject = $configurableProductObject->load($configurable_id);

                $this->_proccesConfigurable($configurableProductObject);

            }
        }


        if ($_product->getTypeId()=="configurable"){
            $this->_proccesConfigurable($_product);
        }

		
		return $observer; 
    }



    private function _proccesConfigurable($configurableProductObject){

        $configurableProduct = $this->configurableProduct;
        $associatedProductsCollection = $configurableProduct
            ->getUsedProductCollection($configurableProductObject)
            ->addFilterByRequiredOptions()->load();


        $readConnection = $this->resource->getConnection('core_read');

        $tableName = $this->resource->getTableName('cataloginventory_stock_item');

        $associatedIds = array();
        foreach($associatedProductsCollection as $associatedProduct){
            $associatedIds[] = $associatedProduct->getId();
        }
        $stockStatus = 0;
        if ($associatedProductsCollection->count()!=0) {
            $sql = "select count(*) as totalInStock from $tableName where product_id in (" . join(",", $associatedIds) . ") and is_in_stock = 1";
            $result = $readConnection->query($sql)->fetchObject();

            if ($result->totalInStock > 0) {
                $stockStatus = 1;
            }
        }

        /*

        $configurableStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($configurableProductObject);


        $configurableStock->setIsInStock($stockStatus);
        $configurableStock->save();
        */


        $resource = $this->resource;
        $writeConnection = $resource->getConnection('core_write');

        /**
         * Fix to infinite recursive saving via observers
         */
        try {

            $tableName = $resource->getTableName('cataloginventory_stock_item');
            $sql = "update $tableName set is_in_stock = $stockStatus where product_id = ".$configurableProductObject->getId();
            $writeConnection->query($sql);

            $tableName = $resource->getTableName('cataloginventory_stock_status');
            $sql = "update $tableName set stock_status = $stockStatus where product_id = ".$configurableProductObject->getId();
            $writeConnection->query($sql);
        }catch(Exception $ex){
            Mage::logException($ex);
        }


    }


}