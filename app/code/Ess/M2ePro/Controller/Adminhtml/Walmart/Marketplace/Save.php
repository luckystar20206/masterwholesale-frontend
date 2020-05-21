<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Marketplace;

use Ess\M2ePro\Controller\Adminhtml\Walmart\Marketplace;

/**
 * Class Save
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart\Marketplace
 */
class Save extends Marketplace
{
    //########################################

    public function execute()
    {
        $marketplaces = $this->activeRecordFactory->getObject('Marketplace')->getCollection();

        foreach ($marketplaces as $marketplace) {
            $newStatus = $this->getRequest()->getParam('status_'.$marketplace->getId());

            if ($newStatus === null) {
                continue;
            }
            if ($marketplace->getStatus() == $newStatus) {
                continue;
            }
            $marketplace->setData('status', $newStatus)->save();
        }
    }

    //########################################
}
