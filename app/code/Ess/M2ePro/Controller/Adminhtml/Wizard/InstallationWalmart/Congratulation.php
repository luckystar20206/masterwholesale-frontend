<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

/**
 * Class Congratulation
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart
 */
class Congratulation extends InstallationWalmart
{
    public function execute()
    {
        $this->init();

        return $this->congratulationAction();
    }
}
