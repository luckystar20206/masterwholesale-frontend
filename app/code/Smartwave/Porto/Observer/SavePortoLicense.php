<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Smartwave\Porto\Observer;

use Magento\Framework\Event\ObserverInterface;

class SavePortoLicense implements ObserverInterface
{
    protected $_messageManager;
    protected $_cssGenerator;
    private $_helper;
    
    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Smartwave\Porto\Model\Cssconfig\Generator $cssenerator,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Smartwave\Porto\Helper\Data $helper
    ) {
        $this->_cssGenerator = $cssenerator;
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
    }

    /**
     * Log out user and redirect to new admin custom url
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $check = $this->_helper->checkPurchaseCode(true);
        if($check && $check != "localhost") {
            $this->_messageManager->getMessages(true);
            $this->_messageManager->addSuccess( 'Smartwave Porto Theme was activated!' );
        }
    }
}
