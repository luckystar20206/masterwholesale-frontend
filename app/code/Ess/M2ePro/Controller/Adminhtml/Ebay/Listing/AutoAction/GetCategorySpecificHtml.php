<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

/**
 * Class GetCategorySpecificHtml
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
 */
class GetCategorySpecificHtml extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
{
    //########################################

    public function execute()
    {
        // ---------------------------------------
        $listingId = $this->getRequest()->getParam('id');
        $groupId = $this->getRequest()->getParam('group_id');
        $autoMode = $this->getRequest()->getParam('auto_mode');
        $categoryMode = $this->getRequest()->getParam('category_mode');
        $categoryValue = $this->getRequest()->getParam('category_value');
        $listing = $this->ebayFactory->getCachedObjectLoaded('Listing', $listingId);
        // ---------------------------------------

        /** @var $specific \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Category\Settings\Specific */
        $specific = $this->createBlock('Ebay_Listing_Product_Category_Settings_Specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());
        $specific->setCategoryMode($categoryMode);
        $specific->setCategoryValue($categoryValue);

        $categoryWasChanged = false;

        $template = $this->getCategoryTemplate($autoMode, $groupId, $listing);

        if (!$template) {
            $categoryWasChanged = true;
        } else {
            if ($categoryMode == \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_EBAY &&
                $template->getData('category_main_id') != $categoryValue) {
                $categoryWasChanged = true;
            }

            if ($categoryMode == \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_EBAY &&
                $template->getData('category_main_id') != $categoryValue) {
                $categoryWasChanged = true;
            }
        }

        if ($categoryWasChanged) {
            $templateData = [
                'category_main_id'        => 0,
                'category_main_mode'      => $categoryMode,
                'category_main_attribute' => '',
                'marketplace_id'          => $listing->getMarketplaceId()
            ];

            if ($categoryMode == \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_EBAY) {
                $templateData['category_main_id'] = $categoryValue;
            } elseif ($categoryMode == \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_ATTRIBUTE) {
                $templateData['category_main_attribute'] = $categoryValue;
            }

            $existingTemplates = $this->activeRecordFactory->getObject('Ebay_Template_Category')
                ->getCollection()
                ->getItemsByPrimaryCategories([$templateData]);

            $template = reset($existingTemplates);
        }

        if ($template) {
            $specific->setInternalData($template->getData());
            $specific->setSelectedSpecifics($template->getSpecifics());
        }

        $this->setAjaxContent($specific);
        return $this->getResult();
    }

    //########################################
}
