<?php
namespace  ModernRetail\Import\Controller\Adminhtml\Import;


use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Settings extends \ModernRetail\Import\Controller\Adminhtml\Import {



    public function execute(){
        $url = $this->helper->getUrl("adminhtml/system_config/edit",array("section"=>"modernretail_import"));
        $this->_redirect($url);
    }
}