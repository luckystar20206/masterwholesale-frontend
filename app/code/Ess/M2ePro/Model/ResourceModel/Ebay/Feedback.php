<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Ebay;

/**
 * Class Feedback
 * @package Ess\M2ePro\Model\ResourceModel\Ebay
 */
class Feedback extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init('m2epro_ebay_feedback', 'id');
    }

    //########################################
}
