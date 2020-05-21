<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel;

/**
 * Class Wizard
 * @package Ess\M2ePro\Model\ResourceModel
 */
class Wizard extends ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init('m2epro_wizard', 'id');
    }

    //########################################
}
