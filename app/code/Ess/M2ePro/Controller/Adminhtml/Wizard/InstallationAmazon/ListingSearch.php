<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon;

/**
 * Class ListingSearch
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon
 */
class ListingSearch extends InstallationAmazon
{
    public function execute()
    {
        return $this->_redirect('*/amazon_listing_create', ['step' => 3, 'wizard' => true]);
    }
}
