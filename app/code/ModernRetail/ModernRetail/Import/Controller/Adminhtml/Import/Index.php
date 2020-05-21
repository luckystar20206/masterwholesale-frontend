<?php
namespace  ModernRetail\Import\Controller\Adminhtml\Import;


use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \ModernRetail\Import\Controller\Adminhtml\Import {





    public function execute(){

       //d($this->helper->getAttributeValue('color',"Google"));

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('ModernRetail_Import::index');
        $resultPage->addBreadcrumb(__('Modern Retail'), __('Modern Retail'));
        $resultPage->addBreadcrumb(__('Manage Pages'), __('Manage Pages'));
        $resultPage->getConfig()->getTitle()->prepend(__('Modern Retail Import'));

        return $resultPage;
    }
}