<?php
namespace  ModernRetail\TotalReport\Controller\Adminhtml\Report;


use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AllocatedReport extends  \Magento\Backend\App\Action {


    public $helper;
    public $mageHelper;
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * A factory that knows how to create a "page" result
     * Requires an instance of controller action in order to impose page type,
     * which is by convention is determined from the controller action class.
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
        //,\Magento\Backend\Helper\Data $mageHelper

    )
    {
        parent::__construct($context);

        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_resultForwardFactory = $resultForwardFactory;
        //$this->mageHelper = $mageHelper;
    }


    public function execute(){


        //d($this->helper->getAttributeValue('color',"Google"));

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('ModernRetail_TotalReport::allocatedreport');
        $resultPage->getConfig()->getTitle()->prepend(__('ModernRetail Report'));

        return $resultPage;

    }

}