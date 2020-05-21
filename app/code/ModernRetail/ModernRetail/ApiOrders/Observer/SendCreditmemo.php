<?php
namespace ModernRetail\ApiOrders\Observer;
class SendCreditMemo implements \Magento\Framework\Event\ObserverInterface
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
            $cmemo = $observer->getEvent()->getCreditmemo();
            $this->mrApiOrderHelper->sendCreditMemo($cmemo,false);
        }catch (\Exception $ex){
            // sending creditmemo failed - please check logs.
        }
    }


}
