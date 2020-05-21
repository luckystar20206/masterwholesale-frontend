<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Listing\Product\Variation;

/**
 * Class Option
 * @package Ess\M2ePro\Model\ResourceModel\Listing\Product\Variation
 */
class Option extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Parent\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init('m2epro_listing_product_variation_option', 'id');
    }

    //########################################
}
