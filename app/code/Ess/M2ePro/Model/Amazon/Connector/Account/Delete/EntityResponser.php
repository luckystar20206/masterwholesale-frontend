<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Account\Delete;

/**
 * Class EntityResponser
 * @package Ess\M2ePro\Model\Amazon\Connector\Account\Delete
 */
class EntityResponser extends \Ess\M2ePro\Model\Amazon\Connector\Command\Pending\Responser
{
    // ########################################

    protected function validateResponse()
    {
        return true;
    }

    protected function processResponseData()
    {
        return null;
    }

    // ########################################
}
