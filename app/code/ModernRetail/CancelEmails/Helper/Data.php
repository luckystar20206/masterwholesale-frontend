<?php

namespace ModernRetail\CancelEmails\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{


    public function __construct(\Magento\Backend\Model\Session $session)
    {
        $this->_session = $session;

    }

    public function getLastCanceledItems(){

        $session = $this->getSession();
        if($session->getLastCanceledOrderItem()){
            return $session->getLastCanceledOrderItem();
        }
        return [];
    }

    public function getLastItemQty($itemId){

        $lastCanceledItems = $this->getLastCanceledItems();

        if(array_key_exists($itemId,$lastCanceledItems)) return $lastCanceledItems[$itemId];

        return 0;
    }

    protected function getSession(){
        return $this->_session;
    }
}