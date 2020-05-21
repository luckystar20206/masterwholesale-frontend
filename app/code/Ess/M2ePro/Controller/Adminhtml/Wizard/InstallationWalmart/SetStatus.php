<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart;

/**
 * Class SetStatus
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationWalmart
 */
class SetStatus extends InstallationWalmart
{
    public function execute()
    {
        return $this->setStatusAction();
    }
}
