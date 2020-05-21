<?php
namespace Smartwave\Dailydeals\Controller\Deal;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class View extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    protected $resultRedirectFactory;
    protected $scopeConfig;
    
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    
        $this->pageFactory = $pageFactory;
        $this->resultRedirectFactory=$resultRedirectFactory;
        $this->scopeConfig=$scopeConfig;
        
        return parent::__construct($context);
    }

    public function execute()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
     
              $page_object = $this->pageFactory->create();
              return $page_object;
    }
}
