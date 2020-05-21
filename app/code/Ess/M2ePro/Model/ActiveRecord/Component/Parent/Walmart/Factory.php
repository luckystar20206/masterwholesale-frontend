<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ActiveRecord\Component\Parent\Walmart;

/**
 * Class Factory
 * @package Ess\M2ePro\Model\ActiveRecord\Component\Parent\Walmart
 */
class Factory extends \Ess\M2ePro\Model\ActiveRecord\Component\Parent\AbstractFactory
{
    //########################################

    protected function getComponentMode()
    {
        return \Ess\M2ePro\Helper\Component\Walmart::NICK;
    }

    //########################################
}
