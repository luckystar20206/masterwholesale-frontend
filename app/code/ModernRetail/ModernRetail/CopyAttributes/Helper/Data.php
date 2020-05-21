<?php
namespace ModernRetail\CopyAttributes\Helper;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;

class Data  extends AbstractHelper{


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        //\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \ModernRetail\CopyAttributes\Model\Resource\Copy $copy
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->attributeCollection = $attributeCollection;
        $this->storeManager =  $storeManager;
        $this->productAction  = $productAction;
        $this->copy = $copy;
        parent::__construct($context);
    }

    public function getDisabledAttributes(){
        $disabled = explode(",",$this->scopeConfig->getValue("modernretail_copy_attributes/settings/disabled_attributes"));

        return $disabled;
    }

    public function isEnabled(){
      
        return $this->scopeConfig->getValue("modernretail_copy_attributes/settings/is_enabled");
    }

    public function shouldCopyAttribute($attribute_code){

        $attributes = $this->getAttributesForCopy();

        return in_array($attribute_code,$attributes);
    }

    public function getAttributesForCopy(){

        if (!$this->isEnabled()) return [];
        $return = array();
        $disabled = $this->getDisabledAttributes();
        $attributes = $this->scopeConfig->getValue("modernretail_copy_attributes/settings/attributes");
      

        $attributeCodes = explode(",",$attributes);

        $attributes = $this->attributeCollection;
        foreach ($attributes as  $attribute) {
            /**
             * Skip attributes withour label
             */
            if (!$attribute->getFrontendLabel()) continue;

            /**
             * Skip disabled attributes
             */
            if (in_array($attribute->getAttributeCode(), $disabled)) continue;

            if (in_array("ALL", $attributeCodes) || in_array($attribute->getAttributeCode(), $attributeCodes)){
                $return[] = $attribute->getAttributeCode();
            }
        }

        return $return;
    }


    public function getMinQty(){
        return intval($this->scopeConfig->getValue("cataloginventory/item_options/min_qty"));
    }


    public function copyAttributes($_product){


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
        if (!$this->isEnabled()) return $this;


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
        $attributesForCopy = $this->getAttributesForCopy();

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



        $manufacturer = $_product->getAttributeText('manufacturer');

        if ($_product->getAttributeText('manufacturer') != '') {
            $manufacturerText = ' by ' . $_product->getAttributeText('manufacturer');
        } else {
            $manufacturerText = '';
        }

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

    }

}