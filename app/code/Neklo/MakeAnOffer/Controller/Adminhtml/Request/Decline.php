<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Neklo\MakeAnOffer\Model\Source\Status;

class Decline extends Action
{
    const ADMIN_RESOURCE = 'Neklo_MakeAnOffer::main';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Neklo\MakeAnOffer\Model\RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Neklo\MakeAnOffer\Helper\Statistic
     */
    private $statisticHelper;

    /**
     * @var \Neklo\MakeAnOffer\Helper\Email
     */
    private $emailHelper;

    /**
     * Decline constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Neklo\MakeAnOffer\Model\RequestFactory $requestFactory
     * @param \Neklo\MakeAnOffer\Helper\Statistic $statisticHelper
     * @param \Neklo\MakeAnOffer\Helper\Email $emailHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Neklo\MakeAnOffer\Model\RequestFactory $requestFactory,
        \Neklo\MakeAnOffer\Helper\Statistic $statisticHelper,
        \Neklo\MakeAnOffer\Helper\Email $emailHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->requestFactory = $requestFactory;
        $this->statisticHelper = $statisticHelper;
        $this->emailHelper = $emailHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $requestId = $this->getRequest()->getParam('request_id');
        $reasonCopy = $this->getRequest()->getParam('reason_copy');
        $requestItem = $this->requestFactory->create()->load($requestId);
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$requestItem->getId()) {
            $this->messageManager->addErrorMessage(__('This offer is not exist'));
            return $resultRedirect->setPath('*/*/respond', ['request_id' => $requestId]);
        }

        $requestItem->setStatus(Status::DECLINED_REQUEST_STATUS);
        $requestItem->save();

        $this->statisticHelper->updateDeclinedQty($requestItem->getProductSku());

        $this->messageManager->addSuccessMessage(__('Request was successfully declined!'));

        try {
            $this->emailHelper->sendDeclineEmail($requestItem, $reasonCopy);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Sorry, something went wrong with email sending.');
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
