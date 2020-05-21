<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */

namespace Ubertheme\Ubdatamigration\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ubertheme_Ubdatamigration::index');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->getResultPageFactory()->create();
        $resultPage->setActiveMenu('Ubertheme_Ubdatamigration::migrate');
        $resultPage->addBreadcrumb(__('Migrate'), __('Migrate'));

        //get current version of this module
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Module\ResourceInterface');
        $version = $resource->getDbVersion('Ubertheme_Ubdatamigration');

        $resultPage->getConfig()->getTitle()->prepend(__('UB Data Migration Pro (CE)')." - {$version}");

        return $resultPage;
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getResultPageFactory(){
        return $this->resultPageFactory;
    }
}
