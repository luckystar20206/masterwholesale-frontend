<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\Import\Model\System\Config\Source;

class Attributes  implements \Magento\Framework\Option\ArrayInterface
{

    protected $_attributeCollectionFactory = null;

    public function __construct( \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollectionFactory)
    {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
    }


    public function toOptionArray()
    {
        $return = array();

        $this->_attributeCollectionFactory->setEntityTypeFilter(4);

        $attributes = $this->_attributeCollectionFactory->getItems();
        foreach($attributes as $attribute){
            
            if (!$attribute->getFrontendLabel()) continue;
            $return[] = array(
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            );
        }
        return $return;

    }
}
