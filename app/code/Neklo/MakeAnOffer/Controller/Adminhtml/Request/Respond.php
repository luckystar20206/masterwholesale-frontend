<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;

class Respond extends Action
{
    const ADMIN_RESOURCE = 'Neklo_MakeAnOffer::main';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Neklo\MakeAnOffer\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Neklo\MakeAnOffer\Helper\Request
     */
    private $requestModel;

    /**
     * Respond constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Neklo\MakeAnOffer\Helper\Data $dataHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Neklo\MakeAnOffer\Helper\Data $dataHelper,
        \Neklo\MakeAnOffer\Model\Request $requestModel
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->dataHelper = $dataHelper;
        $this->requestModel = $requestModel;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    private function initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Neklo_MakeAnOffer::main')
            ->addBreadcrumb(__('Offer'), __('Offer'))
            ->addBreadcrumb(__('Offer Infomation'), __('Offer Infomation'));
        $resultPage->getConfig()->getTitle()->prepend(__('Respond Form'));
        return $resultPage;
    }

    public function execute()
    {
        $requestId = $this->getRequest()->getParam('request_id');
        if ($requestId) {
            $requestItem = $this->requestModel->load($requestId);
            if (!$requestItem->getId()) {
                $this->messageManager->addError(__('This offer no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $requestItem = $this->dataHelper->prepareDataModel($requestItem);
        $this->coreRegistry->register('current_request', $requestItem);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->initAction();

        return $resultPage;
    }
}
