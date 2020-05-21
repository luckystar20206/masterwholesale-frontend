<?php

namespace WeltPixel\GoogleCards\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Price
 *
 * @package WeltPixel\GoogleCards\Model\Config\Source
 */
class Price implements ArrayInterface
{

    /**
     * Return list of Price Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'incl_tax',
                'label' => __('Incl. Tax')
            ),
            array(
                'value' => 'excl_tax',
                'label' => __('Excl. Tax')
            )
        );
    }
}