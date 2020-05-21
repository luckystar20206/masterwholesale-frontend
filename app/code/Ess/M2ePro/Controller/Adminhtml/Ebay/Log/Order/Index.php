<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Log\Order;

/**
 * Class Index
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Log\Order
 */
class Index extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Log\Order
{
    //########################################

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id', false);

        if ($orderId) {
            $order = $this->ebayFactory->getObjectLoaded('Order', $orderId, 'id', false);

            if ($order === null) {
                $order = $this->ebayFactory->getObject('Order');
            }

            if (!$order->getId()) {
                $this->getMessageManager()->addError($this->__('Listing does not exist.'));
                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend(
                $this->__('Order #%s% Log', $order->getChildObject()->getData('ebay_order_id'))
            );
        } else {
            $this->getResult()->getConfig()->getTitle()->prepend($this->__('Orders Logs & Events'));
        }

        $this->addContent($this->createBlock('Ebay_Log_Order'));
        return $this->getResult();
    }

    //########################################
}
