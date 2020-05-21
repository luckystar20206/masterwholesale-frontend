<?php
namespace  ModernRetail\CopyAttributes\Model\Resource;

class Copy extends \Magento\Catalog\Model\ResourceModel\Product {
    /**
     * Public wrapper for _saveCategories method
     *
     * @param Varien_Object $object
     */
    public function saveCategoryData( $object)
    {
        $this->_saveCategories($object);
    }

}
