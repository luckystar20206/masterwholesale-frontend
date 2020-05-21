<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Template\Category;

use Ess\M2ePro\Controller\Adminhtml\Walmart\Template\Category;

/**
 * Class GetAddSpecificsGridHtml
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart\Template\Category
 */
class GetAddSpecificsGridHtml extends Category
{
    //########################################

    public function execute()
    {
        $gridBlock = $this->prepareGridBlock();
        $this->setAjaxContent($gridBlock->toHtml());
        return $this->getResult();
    }

    //########################################
}
