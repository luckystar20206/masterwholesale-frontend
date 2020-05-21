<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Core
 * @copyright   Copyright (c) 2016-2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Core\Model\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use Mageplaza\Core\Helper\Validate as ValidateHelper;

/**
 * Class Validate
 * @package Mageplaza\Core\Model\Message
 */
class Validate implements MessageInterface
{
    /**
     * @var \Mageplaza\Core\Helper\Validate
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $_needActiveModules = [];

    /**
     * Validate constructor.
     * @param \Mageplaza\Core\Helper\Validate $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        ValidateHelper $helper,
        UrlInterface $urlBuilder
    )
    {
        $this->_helper = $helper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Check whether all extensions are valid or not
     *
     * @return bool
     */
    public function isDisplayed()
    {
        $notActiveModules = $this->getModules();
        if (sizeof($notActiveModules)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getModules()
    {
        if (empty($this->_needActiveModules)) {
            $moduleLists = $this->_helper->getModuleList();
            foreach ($moduleLists as $module) {
                if ($this->_helper->needActive($module) && !$this->_helper->isModuleActive($module)) {
                    $this->_needActiveModules[] = $module;
                }
            }
        }

        return $this->_needActiveModules;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('MAGEPLAZA_VALIDATE_MESSAGE');
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getText()
    {
        $modules = $this->getModules();
        if (empty($modules)) {
            return '';
        }

        $sectionName = $this->_helper->getConfigModulePath($modules[0]);
        $url = $this->urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => $sectionName]);

        return __(
            'One or more Mageplaza extensions are not validated. Click <a href="%1">here</a> to validate them.',
            $url
        );
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
