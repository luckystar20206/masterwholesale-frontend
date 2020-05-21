<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Setup\Upgrade\v1_2_0__v1_3_0;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature;

class IncreaseCapacityOfSystemLogMessage extends AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return [];
    }

    public function execute()
    {
        $this->getTableModifier('system_log')
             ->changeColumn('description', 'LONGTEXT', 'NULL');
    }

    //########################################
}