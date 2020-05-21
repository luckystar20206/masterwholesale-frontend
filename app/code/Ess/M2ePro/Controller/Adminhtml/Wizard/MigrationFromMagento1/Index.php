<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\MigrationFromMagento1;

use Ess\M2ePro\Controller\Adminhtml\Wizard\MigrationFromMagento1;

/**
 * Class Index
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard\MigrationFromMagento1
 */
class Index extends MigrationFromMagento1
{
    public function execute()
    {
        return $this->indexAction();
    }
}
