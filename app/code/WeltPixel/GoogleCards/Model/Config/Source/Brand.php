<?php

namespace WeltPixel\GoogleCards\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Brand
 *
 * @package WeltPixel\GoogleCards\Model\Config\Source
 */
class Brand implements ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Return list of Product Attributes for Brand Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = array(
            'value' => 0,
            'label' => __('Please select')
        );

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addVisibleFilter()
            ->setOrder('frontend_label', 'ASC');

        foreach ($attributeCollection->getItems() as $attribute) {
            $options[] = array(
                'value' => $attribute->getData('attribute_code'),
                'label' => $attribute->getData('frontend_label')
            );
        }

        return $options;
    }
}