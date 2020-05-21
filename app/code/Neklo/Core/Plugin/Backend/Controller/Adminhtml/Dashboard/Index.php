<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Plugin\Backend\Controller\Adminhtml\Dashboard;

class Index
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    public $auth;
    /**
     * @var \Neklo\Core\Model\Feed
     */
    private $feed;

    /**
     * Dashboard constructor.
     *
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Neklo\Core\Model\Feed $feed
     */
    public function __construct(
        \Magento\Backend\Model\Auth $auth,
        \Neklo\Core\Model\Feed $feed
    ) {
        $this->auth = $auth;
        $this->feed = $feed;
    }

    /**
     * @param \Magento\Backend\Controller\Adminhtml\Dashboard\Index $subject
     * @param \Closure $proceed
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Backend\Controller\Adminhtml\Dashboard\Index $subject, \Closure $proceed)
    {
        if ($this->auth->isLoggedIn()) {
            $this->feed->checkUpdate();
        }
        return $proceed();
    }
}
