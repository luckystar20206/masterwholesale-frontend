<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Config;

/**
 * Class Primary
 * @package Ess\M2ePro\Model\ResourceModel\Config
 */
class Primary extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    // ########################################

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('m2epro_primary_config', 'id');
    }

    // ########################################
}
