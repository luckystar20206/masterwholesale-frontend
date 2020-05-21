<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Template\Category;

use Ess\M2ePro\Controller\Adminhtml\Walmart\Template\Category;

/**
 * Class Index
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart\Template\Category
 */
class Index extends Category
{
    //########################################

    public function execute()
    {
        return $this->_redirect('*/walmart_template/index');
    }

    //########################################
}
