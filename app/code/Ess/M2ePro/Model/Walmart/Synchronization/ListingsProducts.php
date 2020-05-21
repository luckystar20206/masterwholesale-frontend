<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Synchronization;

/**
 * Class ListingsProducts
 * @package Ess\M2ePro\Model\Walmart\Synchronization
 */
class ListingsProducts extends AbstractModel
{
    //########################################

    protected function getType()
    {
        return \Ess\M2ePro\Model\Synchronization\Task\AbstractComponent::LISTINGS_PRODUCTS;
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

        $result = !$this->processTask('ListingsProducts_Update_Blocked') ? false : $result;
        $result = !$this->processTask('ListingsProducts\Update') ? false : $result;

        return $result;
    }

    //########################################
}
