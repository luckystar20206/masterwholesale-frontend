<?php
namespace ModernRetail\ApiOrders\Observer;
class SendInvoice implements \Magento\Framework\Event\ObserverInterface
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
    
            $invoice = $observer->getEvent()->getInvoice();

                $this->mrApiOrderHelper->sendInvoice($invoice,false);



    }


}
