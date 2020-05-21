<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

/**
 * Class GetCategoryGroupGrid
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
 */
class GetCategoryGroupGrid extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
{
    //########################################

    public function execute()
    {
        $grid = $this->createBlock('Ebay_Listing_AutoAction_Mode_Category_Group_Grid');
        $this->setAjaxContent($grid);
        return $this->getResult();
    }

    //########################################
}
