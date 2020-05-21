<?php

namespace WeltPixel\GoogleCards\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Description
 *
 * @package WeltPixel\GoogleCards\Model\Config\Source
 */
class Description implements ArrayInterface
{

    /**
     * Return list of Description Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '0',
                'label' => __('Short Description')
            ),
            array(
                'value' => '1',
                'label' => __('Long Description')
            )
        );
    }
}