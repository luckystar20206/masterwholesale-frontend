<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Walmart\Template\SellingFormat;

/**
 * Class ShippingOverride
 * @package Ess\M2ePro\Model\ResourceModel\Walmart\Template\SellingFormat
 */
class ShippingOverride extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init('m2epro_walmart_template_selling_format_shipping_override', 'id');
    }

    //########################################
}
