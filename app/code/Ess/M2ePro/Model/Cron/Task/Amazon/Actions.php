<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Cron\Task\Amazon;

/**
 * Class Actions
 * @package Ess\M2ePro\Model\Cron\Task\Amazon
 */
class Actions extends \Ess\M2ePro\Model\Cron\Task\AbstractModel
{
    const NICK = 'amazon/actions';
    const MAX_MEMORY_LIMIT = 512;

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //####################################

    protected function performActions()
    {
        $actionsProcessor = $this->modelFactory->getObject('Amazon_Actions_Processor');
        $actionsProcessor->setLockItem($this->getLockItem());
        $actionsProcessor->process();
    }

    //####################################
}
