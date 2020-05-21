<?php
namespace  ModernRetail\CopyAttributes\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

class CopyAttributes {



    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \ModernRetail\CopyAttributes\Model\Resource\Copy $copy,
        \ModernRetail\CopyAttributes\Helper\Data $helper
    )
    {
        $this->configurableProduct = $configurableProduct;
        $this->storeManager = $storeManager;
        $this->productAction = $productAction;
        $this->copy = $copy;
        $this->helper = $helper;
    }

    public  function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $_product
    ){




        $helper = $this->helper;
        /**
         * TARGET PRODUCT
         */

        /**
         * proccess description and meta title
         */
        if (!$_product->getMetaTitle())
            $_product->setMetaTitle($_product->getName());

        //if (!$_product->getDescription())
        $_product->setMetaDescription($_product->getDescription());


        /*
         * We need to proccess only configurable products
         */
        if ($_product->getTypeId()!="configurable") return $_product;
        if (!$helper->isEnabled()) return $_product;



        /**
         * =========== START COPY PROCCESS ======
         */

        $storeId = $this->storeManager->getStore()->getId();




        /**
         * get associated products to use as options for current configurable product
         */
        $configurableProduct = $_product->getTypeInstance();

        $associatedProductsCollection = $configurableProduct
            ->getUsedProductCollection($_product)
            ->addAttributeToSelect("meta_title")
            ->addAttributeToSelect("name")
            ->addAttributeToSelect("meta_description")
            ->addAttributeToSelect("description")
            ->addFilterByRequiredOptions()->load();




        $usedAttributes = $configurableProduct->getUsedProductAttributes($_product);

        $associatedProductsIds = $associatedProductsCollection->getColumnValues('entity_id');
        $attributesForCopy = $helper->getAttributesForCopy();

        $attrData = array();

        foreach($_product->getData() as $key=>$value){
            $skip = false;
            foreach($usedAttributes as $usedAttribute){
                if($key==$usedAttribute->getAttributeCode()) {
                    $skip = true;
                    break;
                }
            }

            if (!in_array($key, $attributesForCopy) || $skip===true) continue;

            if (is_array($value)){
                $value = implode(",",$value);
            }

            $attrData[$key] = $value;
        }



        /**
         * Update attributes
         */


        foreach($attrData as $k=>$v){
            $attribute = $this->productAction->getAttribute($k);
            if ($attribute->getBackendType()=="static"){
                unset($attrData[$k]);
            }
            if (is_array($v)){
                unset($attrData[$k]);
            }

        }

        $this->productAction
            ->updateAttributes($associatedProductsIds, $attrData, $storeId);

 
        $categoryIds = array_unique($_product->getCategoryIds());

        $copyResource = $this->copy;

        $productResource = $_product->getResource();
        $productName = $_product->getName();


      
	    /*
        $manufacturer = $_product->getAttributeText('manufacturer');

        if ($_product->getAttributeText('manufacturer') != '') {
            $manufacturerText = ' by ' . $_product->getAttributeText('manufacturer');
        } else {
            $manufacturerText = '';
        }*/

         $metaTitle = $_product['name'];
		 
		 if (!$_product->getMetaTitle()){
		 	    $_product->setMetaTitle($metaTitle); 
		 }
     
	     $_product->setMetaDescription(strip_tags($_product['description']));


        /* Save category ids and url key */
        foreach ($associatedProductsCollection as $product) {
            $product->setData('category_ids', $categoryIds);
            $copyResource->saveCategoryData($product);
            //$product->setData('url_key', $this->createUrlKey($productName, $product));
            //$productResource->saveAttribute($product, 'url_key');

            /**
             * Descriptions and meta titles
             */ 
            if (!$product->getMetaTitle()){
                $metaTitle = $product->getName();
                if ($metaTitle)
                    $metaTitle = $_product->getMetaTitle();

                $product->setData("meta_title",$metaTitle);
                $productResource->saveAttribute($product, 'meta_title');
            }

            if (!$product->getMetaDescription()){
                $metaDescription = $product->getDescription();
                if ($metaDescription)
                    $metaDescription = $_product->getMetaDescription();

                $product->setData("meta_description",$metaDescription);
                $productResource->saveAttribute($product, 'meta_description');
            }
        }



        return $_product;



        /* Register mass action indexer event
        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
        );

		 */

    }


    /**
     * @param string $name
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function createUrlKey($name, $product)
    {
        $color = '-' . $product->getAttributeText('color');
        $size = '-' . $product->getAttributeText('size');
        $name = str_replace(' ', '-', $name);
        return $name . $color . $size;
    }


}