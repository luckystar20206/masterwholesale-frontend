<?php

namespace WeltPixel\GoogleCards\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Id
 *
 * @package WeltPixel\GoogleCards\Model\Config\Source
 */
class Id implements ArrayInterface
{
    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'id',
                'label' => __('ID')
            ),
            array(
                'value' => 'sku',
                'label' => __('SKU')
            )
        );
    }
}
