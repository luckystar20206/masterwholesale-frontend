<?php

namespace Smartwave\Core\Model;

class Feed extends \Magento\AdminNotification\Model\Feed
{
    const SMARTWAVE_FEED_URL = 'www.portotheme.com/envato/porto2_notifications.rss';

    public function getFeedUrl()
    {
        $httpPath = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $httpPath . self::SMARTWAVE_FEED_URL;
        }
        return $this->_feedUrl;
    }

    public function getLastUpdate()
    {
        return $this->_cacheManager->load('smartwave_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'smartwave_notifications_lastcheck');
        return $this;
    }

}
