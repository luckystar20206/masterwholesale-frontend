<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Registry;

/**
 * Class Collection
 * @package Ess\M2ePro\Model\ResourceModel\Registry
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    protected function _construct()
    {
        $this->_init(
            'Ess\M2ePro\Model\Registry',
            'Ess\M2ePro\Model\ResourceModel\Registry'
        );
    }

    //########################################
}
