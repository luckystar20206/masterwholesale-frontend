<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Orders\Refund;

/**
 * Class ItemsResponser
 * @package Ess\M2ePro\Model\Amazon\Connector\Orders\Refund
 */
abstract class ItemsResponser extends \Ess\M2ePro\Model\Amazon\Connector\Command\Pending\Responser
{
    // ########################################

    protected function validateResponse()
    {
        return true;
    }

    // ########################################
}
