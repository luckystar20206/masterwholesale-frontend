<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\OperationHistory;

/**
 * Class Collection
 * @package Ess\M2ePro\Model\ResourceModel\OperationHistory
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            'Ess\M2ePro\Model\OperationHistory',
            'Ess\M2ePro\Model\ResourceModel\OperationHistory'
        );
    }

    //########################################
}
