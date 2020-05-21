<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\CopyAttributes\Model\System\Config\Source;

class Attributes  implements \Magento\Framework\Option\ArrayInterface
{

    protected $_attributeCollectionFactory = null;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollectionFactory,
        \ModernRetail\CopyAttributes\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection
        )
    {
        $this->helper = $helper;
        $this->attributeCollection = $attributeCollection;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
    }


    public function toOptionArray()
    {
        $disabledAttributes = $this->helper->getDisabledAttributes();

        $return = array(
            array('value'=>"ALL", 'label'=>'-- ALL ATTRIBUTES --'),

        );

        $attributes = $this->attributeCollection;

        foreach ($attributes as  $attribute) {
            /**
             * Skip attributes withour label
             */
            if (!$attribute->getFrontendLabel()) continue;

            /**
             * Skip disabled attributes
             */
            if (in_array($attribute->getAttributeCode(), $disabledAttributes)) continue;



            $return[] = array(
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            );
        }

        return $return;
    }


}
