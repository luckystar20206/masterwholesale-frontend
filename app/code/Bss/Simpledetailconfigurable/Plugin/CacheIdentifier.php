<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin;

class CacheIdentifier
{
    /**
     * @var \Bss\Simpledetailconfigurable\Model\CustomUrlFactory
     */
    protected $customUrlFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $moduleConfig;

    /**
     * @param \Bss\Simpledetailconfigurable\Model\CustomUrlFactory $customUrlFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Model\CustomUrlFactory $customUrlFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->customUrlFactory = $customUrlFactory;
        $this->moduleConfig = $moduleConfig;
    }

    public function afterGetValue(
        \Magento\Framework\App\PageCache\Identifier $subject,
        $result
    ) {
        $customUrl = $this->getSdcpUrl($this->request->getUriString());
        $decodedUrl = urldecode($customUrl);
        $decodedUrl = str_replace(' ', '+', $decodedUrl);
        $sdcpUrlCollection = $this->customUrlFactory->create()->getCollection()
        ->getItemByColumnValue('custom_url', $customUrl);
        if ($sdcpUrlCollection) {
            $data = [
                $this->request->isSecure(),
                str_replace($customUrl, $sdcpUrlCollection->getParentUrl(), $this->request->getUriString()),
                $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                    ?: $this->context->getVaryString()
            ];
            return sha1($this->moduleConfig->serialize($data));
        } else {
            return $result;
        }
    }

    private function getSdcpUrl($url)
    {
        $urlPiece = explode('/', $url);
        return end($urlPiece);
    }
}
