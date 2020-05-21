<?php
namespace ModernRetail\CopyAttributes\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class BeforeInventorySave  implements ObserverInterface{



    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \ModernRetail\CopyAttributes\Model\Resource\Copy $copy,
        \ModernRetail\CopyAttributes\Helper\Data $helper
    )
    {
        $this->configurableProduct = $configurableProduct;
        $this->storeManager = $storeManager;
        $this->productAction = $productAction;
        $this->copy = $copy;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
    

    }

}