<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Setup\Upgrade\v1_0_0__v1_1_0;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature;

class ServicingMessages extends AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return ['primary_config'];
    }

    public function execute()
    {
        $this->getConfigModifier('primary')->getEntity('/M2ePro/server/', 'messages')->delete();
    }

    //########################################
}