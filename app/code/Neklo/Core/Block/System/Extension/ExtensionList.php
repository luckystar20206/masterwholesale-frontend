<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/

namespace Neklo\Core\Block\System\Extension;

class ExtensionList extends \Magento\Backend\Block\Template
{

    const DOMAIN = 'https://store.neklo.com/';
    const IMAGE_EXTENSION = '.jpg';

    public $feedData = null;

    /**
     * @var \Neklo\Core\Helper\Extension
     */
    public $extensionHelper;

    /**
     * @var \Neklo\Core\Model\Feed\Extension
     */
    public $feedExtension;

    /**
     * ExtensionList constructor.
     *
     * @param \Neklo\Core\Helper\Extension $extensionHelper
     * @param \Neklo\Core\Model\Feed\Extension $feedExtension
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Neklo\Core\Helper\Extension $extensionHelper,
        \Neklo\Core\Model\Feed\Extension $feedExtension,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->feedExtension = $feedExtension;
        $this->extensionHelper = $extensionHelper;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function canShowExtension($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));
        return !!count($feedData);
    }

    /**
     * @return array
     */
    public function getExtensionList()
    {
        return $this->extensionHelper->getModuleConfigList();
    }

    /**
     * @param string $code
     *
     * @return mixed
     */
    public function getExtensionName($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));

        if (!array_key_exists('name', $feedData)) {
            return $code;
        }

        return $feedData['name'];
    }

    /**
     * @param string $code
     * @param $config
     *
     * @return bool
     */
    public function isExtensionVersionOutdated($code, $config)
    {
        $currentVersion = $this->getExtensionVersion($config);
        $lastVersion = $this->getLastExtensionVersion($code);

        return version_compare($currentVersion, $lastVersion) === -1;
    }

    public function getExtensionVersion($config)
    {
        $version = (string)$config['setup_version'];
        if (!$version) {
            return '';
        }

        return $version;
    }

    public function getLastExtensionVersion($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));
        if (!array_key_exists('version', $feedData)) {
            return '0';
        }

        return $feedData['version'];
    }

    public function getExtensionUrl($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));

        if (!array_key_exists('url', $feedData)) {
            return null;
        }

        return $feedData['url'];
    }

    public function getImageUrl($code)
    {
        $feedData = $this->_getExtensionInfo($code);

        if (!array_key_exists('image', $feedData)) {
            return null;
        }

        return $feedData['image'];
    }

    private function _getExtensionInfo($code)
    {
        if ($this->feedData === null) {
            $this->feedData = $this->feedExtension->getFeed();
        }

        if (!array_key_exists($code, $this->feedData)) {
            return [];
        }

        return $this->feedData[$code];
    }
}
