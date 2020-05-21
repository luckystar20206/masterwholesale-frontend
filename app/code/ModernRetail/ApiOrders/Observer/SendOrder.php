<?php
namespace ModernRetail\ApiOrders\Observer;
class SendOrder implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \ModernRetail\ApiOrders\Helper\Data $mrApiOrderHelper,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->mrApiOrderHelper = $mrApiOrderHelper;
        $this->logger = $logger;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        try {
             $order = $observer->getEvent()->getOrder();
             $this->mrApiOrderHelper->sendOrder($order,false);

        }catch (\Exception $ex){
             // sending orders failed - please check logs.
        }
    }


}


