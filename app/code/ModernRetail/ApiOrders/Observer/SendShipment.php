<?php
namespace ModernRetail\ApiOrders\Observer;
class SendShipment implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \ModernRetail\ApiOrders\Helper\Data $mrApiOrderHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\Manager $_eventManager

    )
    {
        $this->mrApiOrderHelper = $mrApiOrderHelper;
        $this->logger = $logger;
        $this->_eventManager = $_eventManager;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {

            $shipment = $observer->getEvent()->getShipment();
            $this->_eventManager->dispatch('sales_order_save_after', ['order' => $shipment->getOrder()]);
            $this->mrApiOrderHelper->sendShipment($shipment,false);

        }catch (\Exception $ex){
            // sending shipment failed - please check logs.
        }
    }


}
