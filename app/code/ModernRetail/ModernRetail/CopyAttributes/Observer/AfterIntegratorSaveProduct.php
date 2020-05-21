<?php
namespace ModernRetail\CopyAttributes\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class AfterIntegratorSaveProduct  implements ObserverInterface{



    public function __construct(
        \ModernRetail\CopyAttributes\Helper\Data $helper
    )
    {
       $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        $this->helper->copyAttributes($observer->getEvent()->getProduct());
    }





}