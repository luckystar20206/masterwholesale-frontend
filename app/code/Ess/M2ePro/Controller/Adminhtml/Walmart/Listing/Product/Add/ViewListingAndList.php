<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Product\Add;

/**
 * Class ViewListingAndList
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Product\Add
 */
class ViewListingAndList extends \Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Product\Add
{
    //########################################

    public function execute()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            return $this->_redirect('*/walmart_listing/index');
        }

        return $this->_redirect('*/walmart_listing/view', [
            'id' => $listingId,
            'do_list' => true
        ]);
    }

    //########################################
}
