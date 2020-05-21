<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Wizard\InstallationWalmart\Installation\ListingTutorial;

/**
 * Class Content
 * @package Ess\M2ePro\Block\Adminhtml\Wizard\InstallationWalmart\Installation\ListingTutorial
 */
class Content extends \Ess\M2ePro\Block\Adminhtml\Wizard\Installation\ListingTutorial\Content
{
    protected $_template = 'wizard/installationWalmart/installation/listing_tutorial.phtml';

    protected function _beforeToHtml()
    {
        $newListingsUrl = $this->getUrl('*/walmart_listing_create/index', [
            'step' => '1',
            'clear' => 'yes',
        ]);

        $this->jsUrl->add($newListingsUrl, 'walmart_listing_create');

        $buttonBlock = $this
            ->createBlock('Magento\Button')
            ->setData([
                'label'   => $this->__('Create First Listing'),
                'onclick' => 'InstallationWalmartWizardObj.createListing()',
                'class'   => 'primary create-first-listing'
            ]);
        $this->setChild('continue_button', $buttonBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}
