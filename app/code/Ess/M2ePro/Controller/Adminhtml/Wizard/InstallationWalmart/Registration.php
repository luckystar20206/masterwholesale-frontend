<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

/**
 * Class Registration
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart
 */
class Registration extends InstallationWalmart
{
    public function execute()
    {
        $this->init();

        return $this->registrationAction();
    }
}
