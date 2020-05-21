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
namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductInitObserver implements ObserverInterface
{
    private $urlIdentifier;

    private $moduleConfig;

    private $productData;

    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier,
        \Bss\Simpledetailconfigurable\Helper\ProductData $productData,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->urlIdentifier = $urlIdentifier;
        $this->moduleConfig = $moduleConfig;
        $this->productData = $productData;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        $product = $observer->getProduct();
        if (
            $request->getFullActionName() === 'catalog_product_view'
            && $product->getTypeId() === 'configurable'
            && !$this->productData->getEnabledModuleOnProduct($product->getId())->getData('is_ajax_load')
        ) {
            $product = $observer->getProduct();
            $pathInfo = $request->getOriginalPathInfo();
            $product->setSdcpData($pathInfo);
            $moduleConfig = $this->moduleConfig->getAllConfig();
            try {
                $child = $this->urlIdentifier->getChildProduct($this->removeFirstSlashes($pathInfo));
            } catch (\Exception $e) {
                $child = null;
            }
            if ($child) {
                $product->setSdcpPriceInfo($child->getPriceInfo());
                $product->setSdcpId($pathInfo);
                if ($moduleConfig['sku']) {
                    $product->setSku($child->getSku());
                }
                if ($moduleConfig['name']) {
                    $product->setName($child->getName());
                }
                if ($moduleConfig['meta_data']) {
                    if ($child->hasMetaTitle()) {
                        $product->setMetaTitle($child->getMetaTitle());
                    }
                    if ($child->hasMetaKeyword()) {
                        $product->setMetaKeyword($child->getMetaKeyword());
                    }
                    if ($child->hasMetaDescription()) {
                        $product->setMetaDescription($child->getMetaDescription());
                    }
                }
            }
        }
    }

    protected function removeFirstSlashes($pathInfo)
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        if ($firstChar == '/') {
            $pathInfo = ltrim($pathInfo, '/');
        }

        return $pathInfo;
    }
}
