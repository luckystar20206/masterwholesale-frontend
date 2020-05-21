<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon;

/**
 * Class SourceMode
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon
 */
class SourceMode extends InstallationAmazon
{
    public function execute()
    {
        $listingId = $this->amazonFactory->getObject('Listing')->getCollection()->getLastItem()->getId();

        return $this->_redirect(
            '*/amazon_listing_product_add/index',
            [
                'step' => 1,
                'id' => $listingId,
                'new_listing' => true,
                'wizard' => true
            ]
        );
    }
}
