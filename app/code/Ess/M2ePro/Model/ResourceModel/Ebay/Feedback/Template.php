<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Ebay\Feedback;

/**
 * Class Template
 * @package Ess\M2ePro\Model\ResourceModel\Ebay\Feedback
 */
class Template extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init('m2epro_ebay_feedback_template', 'id');
    }

    //########################################
}
