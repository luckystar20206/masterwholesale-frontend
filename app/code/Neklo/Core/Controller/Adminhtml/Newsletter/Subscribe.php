<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Controller\Adminhtml\Newsletter;

class Subscribe extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Neklo_Core::config';

    /**
     * @var \Neklo\Core\Helper\Sender
     */
    public $sender;

    /**
     * Subscribe constructor.
     *
     * @param \Neklo\Core\Helper\Sender $sender
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Neklo\Core\Helper\Sender $sender,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->sender = $sender;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $result['success'] = true;
        try {
            $data = $this->getRequest()->getPost();
            $this->sender->sendData($data);
        } catch (\Exception $e) {
            $result['success'] = false;
            $this->getResponse()->setBody(\Zend_Json::encode($result));
        }

        $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
