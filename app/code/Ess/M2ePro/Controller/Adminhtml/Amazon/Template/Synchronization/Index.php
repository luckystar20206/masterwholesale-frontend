<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Synchronization;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Template;

/**
 * Class Index
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Synchronization
 */
class Index extends Template
{
    public function execute()
    {
        return $this->_redirect('*/amazon_template/index');
    }
}
