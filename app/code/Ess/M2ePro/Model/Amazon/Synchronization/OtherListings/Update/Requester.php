<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\OtherListings\Update;

/**
 * Class Requester
 * @package Ess\M2ePro\Model\Amazon\Synchronization\OtherListings\Update
 */
class Requester extends \Ess\M2ePro\Model\Amazon\Connector\Inventory\Get\ItemsRequester
{
    // ########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Synchronization_OtherListings_Update_ProcessingRunner';
    }

    // ########################################
}
