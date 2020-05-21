<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Amazon;

/**
 * Class Account
 * @package Ess\M2ePro\Model\ResourceModel\Amazon
 */
class Account extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Child\AbstractModel
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('m2epro_amazon_account', 'account_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################
}
