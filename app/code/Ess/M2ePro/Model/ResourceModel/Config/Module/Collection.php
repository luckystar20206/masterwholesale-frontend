<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Config\Module;

/**
 * Class Collection
 * @package Ess\M2ePro\Model\ResourceModel\Config\Module
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    // ########################################

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Ess\M2ePro\Model\Config\Module',
            'Ess\M2ePro\Model\ResourceModel\Config\Module'
        );
    }

    // ########################################
}
