<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Settings\Motors;

/**
 * Class RemoveItemFromProduct
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Settings\Motors
 */
class RemoveItemFromProduct extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing
{
    //########################################

    public function execute()
    {
        $itemsIds = $this->getRequest()->getParam('items_ids');
        $entityId = $this->getRequest()->getParam('entity_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        if (!is_array($itemsIds)) {
            $itemsIds = explode(',', $itemsIds);
        }

        $listingProduct = $this->ebayFactory->getObjectLoaded('Listing\Product', $entityId);

        $motorsAttribute = $this->getHelper('Component_Ebay_Motors')->getAttribute($motorsType);
        $attributeValue = $listingProduct->getMagentoProduct()->getAttributeValue($motorsAttribute);

        $motorsData = $this->getHelper('Component_Ebay_Motors')->parseAttributeValue($attributeValue);

        foreach ($itemsIds as $itemId) {
            unset($motorsData['items'][$itemId]);
        }

        $attributeValue = $this->getHelper('Component_Ebay_Motors')->buildAttributeValue($motorsData);

        $this->activeRecordFactory->getObject('Ebay\Listing')->getResource()->updateMotorsAttributesData(
            $listingProduct->getListingId(),
            [$entityId],
            $motorsAttribute,
            $attributeValue,
            true
        );

        $this->setAjaxContent(0, false);

        return $this->getResult();
    }

    //########################################
}
