<?php

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Listing;

use Ess\M2ePro\Controller\Adminhtml\Walmart\Main;

/**
 * Class GetEditSkuPopup
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart\Listing
 */
class GetEditSkuPopup extends Main
{
    public function execute()
    {
        $this->setAjaxContent(
            $this->createBlock('Walmart_Listing_View_Walmart_Sku_Main')
        );

        return $this->getResult();
    }
}
