<?php
namespace Smartwave\Porto\Controller\Adminhtml\System\Config\Demo;
use Magento\Framework\Controller\ResultFactory;

class GenerateCss extends \Magento\Backend\App\Action {
    protected $_objectManager;
    protected $_publicActions = ['generatecss'];

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);

        $this->_objectManager= $objectManager;
    }
    public function execute()
    {
        $this->_objectManager->get('Smartwave\Porto\Model\Cssconfig\Generator')->generateCss('design','','');
        $this->_objectManager->get('Smartwave\Porto\Model\Cssconfig\Generator')->generateCss('settings','','');

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}