<?php
namespace ModernRetail\Base\Controller\Adminhtml\Base;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action {

    protected $helper;

    protected $_resultPageFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);

        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $context->getHelper();

    }




    public function execute(){
        $url = $this->helper->getUrl("adminhtml/system_config/edit",array("section"=>"modernretail_base"));
        $this->_redirect($url);
    }

   
}