<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Model\Feed;

class Extension
{

    const FEED_URL = 'https://store.neklo.com/feed.json';
    const CACHE_ID = 'NEKLO_EXTENSION_FEED';
    const CACHE_LIFETIME = 172800;

    /**
     * @var \Magento\Framework\App\Cache
     */
    public $cacheManager;
    /**
     * @var \Neklo\Core\Helper\Serializer $json
     */
    public $jsonHelper;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;

    /**
     * Extension constructor.
     *
     * @param \Magento\Framework\App\Cache $cacheManager
     * @param \Neklo\Core\Helper\Serializer $json
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        \Magento\Framework\App\Cache $cacheManager,
        \Neklo\Core\Helper\Serializer $json,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->cacheManager = $cacheManager;
        $this->jsonHelper = $json;
        $this->curl = $curl;
    }

    public function getFeed()
    {
        if (!$feed = $this->cacheManager->load(self::CACHE_ID)) {
            $feed = $this->getFeedFromResource();
            if (!empty($this->jsonHelper->unserialize($feed))) {
                $this->save($feed);
            }
        }

        $feedArray = $this->jsonHelper->unserialize($feed);
        if (!is_array($feedArray)) {
            $feedArray = [];
        }

        return $feedArray;
    }

    private function getFeedFromResource()
    {
        $params = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => 'Content-Type: application/json'

        ];
        $this->curl->post(self::FEED_URL, $params);

        if ($this->curl->getStatus() == 200) {
            $result = $this->curl->getBody();
        } else {
            $result = '{}';
        }

        return $result;
    }

    private function save($feed)
    {
        $this->cacheManager->save($feed, self::CACHE_ID, [], self::CACHE_LIFETIME);

        return $this;
    }
}
