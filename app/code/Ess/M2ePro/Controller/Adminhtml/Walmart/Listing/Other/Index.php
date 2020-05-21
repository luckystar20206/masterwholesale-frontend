<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Other;

/**
 * Class Index
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Other
 */
class Index extends \Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Other
{
    public function execute()
    {
        $this->addContent($this->createBlock('Walmart_Listing_Other'));
        $this->getResultPage()->getConfig()->getTitle()->prepend($this->__('3rd Party Listings'));
        $this->setPageHelpLink('x/UgBhAQ');

        return $this->getResult();
    }
}
