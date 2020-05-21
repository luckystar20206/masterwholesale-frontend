<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\Import\Model\System\Config\Source;

class NewProductStatus  implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
      $return = array(
            array('value'=>"1", 'label'=>'Enabled'),
            array('value'=>"2", 'label'=>'Disabled')
        );
        return $return;

    }
}
