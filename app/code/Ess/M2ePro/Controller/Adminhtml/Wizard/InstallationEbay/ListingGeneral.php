<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationEbay;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationEbay;

/**
 * Class ListingGeneral
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationEbay
 */
class ListingGeneral extends InstallationEbay
{
    public function execute()
    {
        return $this->_redirect('*/ebay_listing_create', ['step' => 2, 'wizard' => true]);
    }
}
