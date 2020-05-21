<?php


namespace ModernRetail\ApiOrders\Observer;
class PlaceOrder implements \Magento\Framework\Event\ObserverInterface
{




    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Event\ManagerInterface $manager */
        $manager = $om->get('Magento\Framework\Event\ManagerInterface');


        


    }


}
