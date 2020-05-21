<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart;

/**
 * Class Account
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart
 */
abstract class Account extends \Ess\M2ePro\Controller\Adminhtml\Walmart\Main
{
    //########################################

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ess_M2ePro::walmart_configuration_accounts');
    }

    //########################################
}
