<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\Import\Model\System\Config\Source;

class AttributeSets  implements \Magento\Framework\Option\ArrayInterface
{

    protected $_attributeSetCollection = null;

    public function __construct( \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSetCollection)
    {
        $this->_attributeSetCollection = $attributeSetCollection;
    }


    public function toOptionArray()
    {

        $this->_attributeSetCollection->setEntityTypeFilter(4);
       return $this->_attributeSetCollection->toOptionArray();

    }
}
