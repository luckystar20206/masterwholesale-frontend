<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Helper;

class Sender extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONTACT_URL = 'https://store.neklo.com/neklo_support/index/index/';

    const SUBSCRIBE_URL = 'https://store.neklo.com/neklo_subscribe/index/index/';
    /**
     * @var \Neklo\Core\Helper\Serializer
     */
    public $jsonHelper;
    /**
     * @var \Magento\Framework\Url\Encoder
     */
    public $urlEncoder;
    /**
     * @var \Magento\Framework\HTTP\ZendClient
     */
    public $zendClient;

    /**
     * Sender constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\ZendClient $zendClient
     * @param \Magento\Framework\Url\Encoder $urlEncoder
     * @param \Neklo\Core\Helper\Serializer $json
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\ZendClient $zendClient,
        \Magento\Framework\Url\Encoder $urlEncoder,
        \Neklo\Core\Helper\Serializer $json
    ) {
        $this->jsonHelper = $json;
        $this->urlEncoder = $urlEncoder;
        $this->zendClient = $zendClient;
        parent::__construct($context);
    }

    /**
     * @param $data
     * @return void
     */
    public function sendData($data)
    {
        $url = isset($data['url']) ? self::CONTACT_URL : self::SUBSCRIBE_URL;
        $data = $this->urlEncoder->encode($this->jsonHelper->serialize($data));
        $this->zendClient->setMethod(\Magento\Framework\HTTP\ZendClient::POST)
                         ->setUri($url)
                         ->setConfig(['maxredirects' => 0, 'timeout' => 30])
                         ->setRawData($data)
                         ->request();
    }
}
