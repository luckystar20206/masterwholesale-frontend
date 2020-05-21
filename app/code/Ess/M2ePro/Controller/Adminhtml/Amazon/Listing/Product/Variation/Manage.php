<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Variation;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Main;

/**
 * Class Manage
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Variation
 */
class Manage extends Main
{
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            $this->setAjaxContent('You should provide correct parameters.', false);

            return $this->getResult();
        }

        $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $productId);
        $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();

        $tabs = $this->createBlock('Amazon_Listing_Product_Variation_Manage_Tabs');
        $tabs->setListingProduct($listingProduct);

        $this->setAjaxContent($tabs);

        return $this->getResult();
    }
}
