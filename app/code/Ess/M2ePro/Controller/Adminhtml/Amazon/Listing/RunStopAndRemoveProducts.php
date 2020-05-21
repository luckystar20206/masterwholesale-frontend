<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing;

/**
 * Class RunStopAndRemoveProducts
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Listing
 */
class RunStopAndRemoveProducts extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\ActionAbstract
{
    public function execute()
    {
        $this->setJsonContent($this->processConnector(
            \Ess\M2ePro\Model\Listing\Product::ACTION_STOP,
            ['remove' => true]
        ));

        return $this->getResult();
    }
}
