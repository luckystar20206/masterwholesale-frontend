<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationEbay;

use Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationEbay;

/**
 * Class Congratulation
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationEbay
 */
class Congratulation extends InstallationEbay
{
    public function execute()
    {
        $this->init();

        return $this->congratulationAction();
    }
}
