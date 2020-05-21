<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Account;

use Ess\M2ePro\Controller\Adminhtml\Ebay\Account;

/**
 * Class NewAction
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Account
 */
class NewAction extends Account
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
