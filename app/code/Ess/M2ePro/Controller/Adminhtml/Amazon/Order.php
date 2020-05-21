<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon;

/**
 * Class Order
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon
 */
abstract class Order extends Main
{
    //########################################

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ess_M2ePro::amazon_sales_orders');
    }

    //########################################

    protected function init()
    {
        $this->addCss('order.css');
        $this->addCss('switcher.css');
        $this->addCss('amazon/order/grid.css');

        $this->getResultPage()->getConfig()->getTitle()->prepend($this->__('Sales'));
        $this->getResultPage()->getConfig()->getTitle()->prepend($this->__('Orders'));
    }

    //########################################
}
