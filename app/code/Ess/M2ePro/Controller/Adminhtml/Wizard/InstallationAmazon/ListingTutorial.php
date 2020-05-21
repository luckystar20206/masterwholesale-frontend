<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon;

/**
 * Class ListingTutorial
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon
 */
class ListingTutorial extends InstallationAmazon
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
