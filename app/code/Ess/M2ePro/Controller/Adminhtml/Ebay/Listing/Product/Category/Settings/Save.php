<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Product\Category\Settings;

use \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Product\Category\Settings;

/**
 * Class Save
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Product\Category\Settings
 */
class Save extends Settings
{

    //########################################

    public function execute()
    {
        $this->save($this->getSessionValue($this->getSessionDataKey()));

        return $this->getResult();
    }

    //########################################
}
