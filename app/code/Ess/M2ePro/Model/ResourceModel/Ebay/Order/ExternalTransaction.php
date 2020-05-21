<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Ebay\Order;

/**
 * Class ExternalTransaction
 * @package Ess\M2ePro\Model\ResourceModel\Ebay\Order
 */
class ExternalTransaction extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('m2epro_ebay_order_external_transaction', 'id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################
}
