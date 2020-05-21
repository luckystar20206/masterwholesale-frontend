<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product\Variation;

/**
 * Class Option
 * @package Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product\Variation
 */
class Option extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Child\AbstractModel
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init(
            'm2epro_amazon_listing_product_variation_option',
            'listing_product_variation_option_id'
        );
        $this->_isPkAutoIncrement = false;
    }

    //########################################
}
