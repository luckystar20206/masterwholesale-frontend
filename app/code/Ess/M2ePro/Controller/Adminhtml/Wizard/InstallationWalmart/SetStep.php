<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

/**
 * Class SetStep
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart
 */
class SetStep extends InstallationWalmart
{
    public function execute()
    {
        return $this->setStepAction();
    }
}
