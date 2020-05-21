<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel;

/**
 * Class Marketplace
 * @package Ess\M2ePro\Model\ResourceModel
 */
class Marketplace extends ActiveRecord\Component\Parent\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init('m2epro_marketplace', 'id');
    }

    //########################################
}
