<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    const NOTIFICATION_TYPE = 'neklo_core/notification/type';
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    public $backendConfig;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        parent::__construct($context);
        $this->backendConfig = $backendConfig;
    }

    /**
     * @return array
     */
    public function getNotificationTypeList()
    {
        return explode(',', $this->backendConfig->getValue(self::NOTIFICATION_TYPE));
    }
}
