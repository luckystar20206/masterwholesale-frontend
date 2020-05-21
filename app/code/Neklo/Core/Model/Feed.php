<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;

class Feed extends \Magento\AdminNotification\Model\Feed
{

    const XML_USE_HTTPS_PATH    = 'neklo_core/notification/use_https';
    const XML_FEED_URL_PATH     = 'neklo_core/notification/feed_url';
    const XML_FREQUENCY_PATH    = 'neklo_core/notification/frequency';

    const LAST_CHECK_CACHE_KEY  = 'neklo_core_admin_notifications_last_check';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var \Neklo\Core\Helper\Config
     */
    public $configHelper;

    /**
     * @var \Neklo\Core\Helper\Extension
     */
    public $extensionHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Neklo\Core\Helper\Extension $extensionHelper,
        \Neklo\Core\Helper\Config $configHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context, $registry, $backendConfig, $inboxFactory, $curlFactory, $deploymentConfig, $productMetadata,
            $urlBuilder, $resource, $resourceCollection, $data
        );
        $this->extensionHelper = $extensionHelper;
        $this->configHelper = $configHelper;
    }

    public function getFrequency()
    {
        return $this->_backendConfig->getValue(self::XML_FREQUENCY_PATH) * 3600;
    }

    public function getLastUpdate()
    {
        return $this->_cacheManager->load(self::LAST_CHECK_CACHE_KEY);
    }

    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), self::LAST_CHECK_CACHE_KEY);
        return $this;
    }

    public function getFeedUrl()
    {
        $path = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $path . $this->_backendConfig->getValue(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = [];

        $feedXml = $this->getFeedData();

        $installDate = strtotime($this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE));

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                if (!$this->isAllowedItem($item)) {
                    continue;
                }
                $itemPublicationDate = strtotime((string)$item->pubDate);
                if ($installDate <= $itemPublicationDate) {
                    $feedData[] = [
                        'severity' => (int)$item->severity,
                        'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                        'title' => htmlspecialchars((string)$item->title),
                        'description' => htmlspecialchars((string)$item->description),
                        'url' => htmlspecialchars((string)$item->link),
                    ];
                }
            }
            if ($feedData) {
                $this->_inboxFactory->create()->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();

        return $this;
    }

    public function isAllowedItem($item)
    {
        $itemType = $item->type ? $item->type : \Neklo\Core\Model\Source\Subscription\Type::INFO_CODE;
        $allowedTypeList = $this->configHelper->getNotificationTypeList();
        if ($itemType == \Neklo\Core\Model\Source\Subscription\Type::UPDATE_CODE) {
            if (in_array(\Neklo\Core\Model\Source\Subscription\Type::UPDATE_ALL_CODE, $allowedTypeList)) {
                return true;
            }
            if (in_array(\Neklo\Core\Model\Source\Subscription\Type::UPDATE_CODE, $allowedTypeList)) {
                $installedList = array_keys($this->extensionHelper->getModuleList());
                $isPresent = false;
                foreach ($item->extension->children() as $extensionCode) {
                    if (in_array((string)$extensionCode, $installedList)) {
                        $isPresent = true;
                    }
                }
                return $isPresent;
            }
        }
        if (!in_array($itemType, $allowedTypeList)) {
            return false;
        }
        return true;
    }
}
