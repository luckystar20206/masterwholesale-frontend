<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Synchronization;

/**
 * Class Launcher
 * @package Ess\M2ePro\Model\Walmart\Synchronization
 */
class Launcher extends AbstractModel
{
    //########################################

    protected function getType()
    {
        return null;
    }

    protected function getNick()
    {
        return null;
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('General') ? false : $result;
        $result = !$this->processTask('ListingsProducts') ? false : $result;
        $result = !$this->processTask('Orders') ? false : $result;
        $result = !$this->processTask('OtherListings') ? false : $result;
        $result = !$this->processTask('Templates') ? false : $result;
        $result = !$this->processTask('Marketplaces') ? false : $result;

        return $result;
    }

    //########################################
}
