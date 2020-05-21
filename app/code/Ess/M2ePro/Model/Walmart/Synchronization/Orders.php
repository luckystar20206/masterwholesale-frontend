<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Synchronization;

/**
 * Class Orders
 * @package Ess\M2ePro\Model\Walmart\Synchronization
 */
class Orders extends AbstractModel
{
    //########################################

    protected function getType()
    {
        return \Ess\M2ePro\Model\Synchronization\Task\AbstractComponent::ORDERS;
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

        $result = !$this->processTask('Orders\Receive') ? false : $result;
        $result = !$this->processTask('Orders\Acknowledge') ? false : $result;
        $result = !$this->processTask('Orders\Shipping') ? false : $result;
        $result = !$this->processTask('Orders\Cancel') ? false : $result;
        $result = !$this->processTask('Orders\Refund') ? false : $result;

        return $result;
    }

    //########################################
}
