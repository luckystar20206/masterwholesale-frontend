<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Main;

/**
 * Class VariationReset
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product
 */
class VariationReset extends Main
{
    public function execute()
    {
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId) {
            $this->setJsonContent([
                'type' => 'error',
                'message' => $this->__(
                    'For changing the Mode of working with Magento Variational Product
                     you have to choose the Specific Product.'
                )
            ]);
            return $this->getResult();
        }

        /** @var $listingProduct \Ess\M2ePro\Model\Listing\Product */
        $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $listingProductId);

        $listingProduct->getChildObject()->setData('search_settings_status', null);
        $listingProduct->getChildObject()->setData('search_settings_data', null);
        $listingProduct->save();

        $listingProductManager = $listingProduct->getChildObject()->getVariationManager();
        if ($listingProductManager->isIndividualType() && $listingProductManager->modeCanBeSwitched()) {
            $listingProductManager->switchModeToAnother();
        }

        $listingProductManager->getTypeModel()->getProcessor()->process();

        $this->setJsonContent([
            'type' => 'success',
            'message' => $this->__(
                'Mode of working with Magento Variational Product has been switched to work with Parent-Child Product.'
            )
        ]);

        return $this->getResult();
    }
}
