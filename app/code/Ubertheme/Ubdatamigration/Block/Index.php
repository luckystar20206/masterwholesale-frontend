<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */

namespace Ubertheme\Ubdatamigration\Block;

class Index extends \Magento\Backend\Block\Template
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\Filesystem $_fileSystem
     */
    protected $_fileSystem;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context)
    {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_fileSystem = $context->getFilesystem();
        parent::__construct($context);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
    
    public function getToolUrl() {
        $pubFolder = \Magento\Framework\App\Filesystem\DirectoryList::PUB;

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore(0);

        $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, $store->isUrlSecure());
        $baseUrl = str_replace("/".$store->getCode()."/", '/', $baseUrl);

        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        if (substr($documentRoot, (strlen($documentRoot) - 3)) == $pubFolder) {
            $toolUrl = $baseUrl . 'ub-tool';
        } else {
            $toolUrl = $baseUrl . $pubFolder.'/ub-tool';
        }
        $toolUrl = str_replace('index.php/', '', $toolUrl);

        //append access token
        $toolUrl .= "/index.php?token=".$this->getToken();

        return $toolUrl;
    }

    public function getToken() {
        $token = '';
        $configPath = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::CONFIG)->getAbsolutePath();
        $configFilePath = "{$configPath}env.php";
        if (file_exists($configFilePath)) {
            $configData = require $configFilePath;
            $token = md5($configData['backend']['frontName'].":".$configData['crypt']['key']);
        }

        return $token;
    }
}
