<?php
namespace ModernRetail\Import\Helper;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;

class SpecialPrice  extends AbstractHelper{


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {

        $this->resource = $resource;
        $this->eavAttribute = $eavAttribute;
        parent::__construct($context);
    }


    public function cleanSpecialPriceForAllProducts(){
        $readConnection = $this->resource->getConnection('core_read');
        $writeConnection = $this->resource->getConnection('core_write');

        $specialPriceAttributeId = $this->eavAttribute->getIdByCode('catalog_product', 'special_price');
        $specialFromDateAttributeId = $this->eavAttribute->getIdByCode('catalog_product', 'special_from_date');
        $specialToDateAttributeId = $this->eavAttribute->getIdByCode('catalog_product', 'special_to_date');

        $catalog_product_entity_datetime = $this->resource->getTableName('catalog_product_entity_datetime');
        $catalog_product_entity_decimal = $this->resource->getTableName('catalog_product_entity_decimal');


        $sql_get_ids = "select GROUP_CONCAT(entity_id) from $catalog_product_entity_datetime where value < NOW() AND attribute_id = $specialToDateAttributeId";
        //ci(   $readConnection->query($sql_get_ids));
        $ids = $readConnection->query($sql_get_ids)->fetchColumn();
        $ids = explode(",",$ids);
        $ids = array_filter($ids);
        $ids = join(",",$ids);

        if (!$ids) return $this;

        /**
         * Delete special_price_values
         */
        $sql = "delete from $catalog_product_entity_decimal where entity_id in ($ids) and attribute_id = $specialPriceAttributeId";
        $writeConnection->query($sql);

        /**
         * Delete from dates values
         */
        $sql = "delete from $catalog_product_entity_datetime where entity_id in ($ids) and attribute_id = $specialFromDateAttributeId";
        $writeConnection->query($sql);
        /**
         * delete toDates values
         */
        $sql = "delete from $catalog_product_entity_datetime where entity_id in ($ids) and attribute_id = $specialToDateAttributeId";
        $writeConnection->query($sql);


        return $this;

    }


    /**
     * @param $product
     * @return $product
     */
    public function processProduct($product){


        




        return $product;
    }




}