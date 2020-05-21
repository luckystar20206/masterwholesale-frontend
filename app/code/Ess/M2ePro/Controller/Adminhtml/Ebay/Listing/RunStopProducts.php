<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing;

/**
 * Class RunStopProducts
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Listing
 */
class RunStopProducts extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\ActionAbstract
{
    public function execute()
    {
        $this->setJsonContent($this->processConnector(\Ess\M2ePro\Model\Listing\Product::ACTION_STOP));

        return $this->getResult();
    }
}
