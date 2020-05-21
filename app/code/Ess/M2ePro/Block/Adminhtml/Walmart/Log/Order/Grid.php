<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Walmart\Log\Order;

/**
 * Class Grid
 * @package Ess\M2ePro\Block\Adminhtml\Walmart\Log\Order
 */
class Grid extends \Ess\M2ePro\Block\Adminhtml\Log\Order\AbstractGrid
{
    //########################################

    protected function getComponentMode()
    {
        return \Ess\M2ePro\Helper\View\Walmart::NICK;
    }

    //########################################
}
