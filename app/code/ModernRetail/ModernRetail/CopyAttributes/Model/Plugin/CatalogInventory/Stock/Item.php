<?php namespace ModernRetail\CopyAttributes\Model\Plugin\CatalogInventory\Stock;


class Item {


    public function __construct()
    {
       
    }

    public function beforeSave(){

        
        $ret  = parent::_beforeSave();

        if ($this->getOrigData("qty") != $this->getQty()){
            if ($this->getQty()>$this->getMinQty()){
                $this->setIsInStock(1);
            }else {
                $this->setIsInStock(0);
            }
        }
        return $ret;

    }
}