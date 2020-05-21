<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Walmart\Listing\Product\Variation\Individual;

/**
 * Class Manage
 * @package Ess\M2ePro\Block\Adminhtml\Walmart\Listing\Product\Variation\Individual
 */
class Manage extends \Ess\M2ePro\Block\Adminhtml\Walmart\Listing\Product\Variation\Individual
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingProductVariationEdit');
        // ---------------------------------------

        $this->setTemplate('walmart/listing/product/variation/individual/manage.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->_prepareButtons();

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _prepareButtons()
    {
        $buttonBlock = $this->createBlock('Magento\Button')->setData([
            'label' => $this->__('Add Another Variation'),
            'onclick' => '',
            'class' => 'action primary',
            'id' => 'add_more_variation_button'
        ]);
        $this->setChild('add_more_variation_button', $buttonBlock);

        // ---------------------------------------

        $onClick = 'WalmartListingProductVariationObj.manageGenerateAction(false);';
        $buttonBlock = $this->createBlock('Magento\Button')->setData([
            'label' => $this->__('Generate All Variations'),
            'onclick' => $onClick,
            'class' => 'action primary',
            'id' => 'variation_manage_generate_all'
        ]);
        $this->setChild('variation_manage_generate_all', $buttonBlock);

        $onClick = 'WalmartListingProductVariationObj.manageGenerateAction(true);';
        $buttonBlock = $this->createBlock('Magento\Button')->setData([
            'label' => $this->__('Generate Non-Existing Variations'),
            'onclick' => $onClick,
            'class' => 'action primary',
            'id' => 'variation_manage_generate_unique'
        ]);
        $this->setChild('variation_manage_generate_unique', $buttonBlock);
    }

    //########################################
}
