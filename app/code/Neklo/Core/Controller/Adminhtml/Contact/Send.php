<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Controller\Adminhtml\Contact;

class Send extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Neklo_Core::config';

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    public $metadata;
    /**
     * @var \Neklo\Core\Helper\Sender
     */
    public $sender;

    /**
     * Send constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\ProductMetadata $metadata
     * @param \Neklo\Core\Helper\Sender $sender
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ProductMetadata $metadata,
        \Neklo\Core\Helper\Sender $sender
    ) {
        parent::__construct($context);
        $this->metadata = $metadata;
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
            $data['version'] = $this->metadata->getVersion();
            $data['url'] = $this->_url->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $data['id'] = '<order_item_customer></order_item_customer>';
            $this->sender->sendData($data);
        } catch (\Exception $e) {
            $result['success'] = false;
        }
        $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
