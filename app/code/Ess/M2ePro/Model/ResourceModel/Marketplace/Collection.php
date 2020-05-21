<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Marketplace;

/**
 * Class Collection
 * @package Ess\M2ePro\Model\ResourceModel\Marketplace
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\Parent\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Ess\M2ePro\Model\Marketplace',
            'Ess\M2ePro\Model\ResourceModel\Marketplace'
        );
    }

    //########################################
}
