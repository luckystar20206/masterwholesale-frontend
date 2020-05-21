<?php

namespace ModernRetail\CancelEmails\Observer;

class CancelEmails implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        return false;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Event\ManagerInterface $manager */
        $manager = $om->get('Magento\Framework\Event\ManagerInterface');
        $emailSender = $om->create('ModernRetail\CancelEmails\Model\EmailSender');
        $emailSender->send($observer->getEvent()->getOrder());
    }
}
