<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Category\Settings\Chooser\Tabs;

/**
 * Class Search
 * @package Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Category\Settings\Chooser\Tabs
 */
class Search extends \Ess\M2ePro\Block\Adminhtml\Magento\AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategoryChooserSearch');
        // ---------------------------------------

        // Set template
        // ---------------------------------------
        $this->setTemplate('ebay/listing/product/category/settings/chooser/tabs/search.phtml');
        // ---------------------------------------
    }

    //########################################
}
