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
namespace Bss\Simpledetailconfigurable\Helper;

class UrlIdentifier extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $moduleConfig;

    private $urlRewriteFactory;

    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->configurableData = $configurableData;
        $this->productInfo = $productInfo;
    }

    public function readUrl($url)
    {
        $result = ['product' => '0'];
        $productInfo = explode('+', $url);
        $urlPart = explode('/', $productInfo[0]);
        array_shift($urlPart);
        $productKey = implode('/', $urlPart);
        $urlRewrite = $this->getProductId($productKey);
        if ($urlRewrite) {
            $result['product'] = $urlRewrite->getEntityId();
            if ($urlRewrite->getMetadata() && isset($urlRewrite->getMetadata()['category_id'])) {
                $result['category'] = $urlRewrite->getMetadata()['category_id'];
            } else {
                $result['category'] = null;
            }
        }
        return $result;
    }

    public function getProductId($urlKey)
    {
        return $this->urlRewriteFactory->create()->getCollection()
        ->addFieldToFilter('entity_type', 'product')
        ->addFieldToFilter('request_path', ['like' => $urlKey . $this->moduleConfig->getSuffix()])
        ->getItemByColumnValue('store_id', $this->moduleConfig->getStoreId());
    }

    public function getChildProduct($url)
    {
        $superData = explode('+', $url);
        $product = array_shift($superData);
        $rewriteModel = $this->getProductId($product);
        if (!$rewriteModel) {
            return null;
        }
        $productId = $rewriteModel->getEntityId();
        $product = $this->productInfo->getById($productId);
        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        foreach ($parentAttribute as $attrKey => $attrValue) {
            $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
            $map[$attrCode] = $attrValue->getAttributeId();
            foreach ($product->getAttributes()[$attrValue->getProductAttribute()->getAttributeCode()]
                ->getOptions() as $tvalue) {
                $map2[$attrValue->getAttributeId()][$tvalue->getLabel()] = $tvalue->getValue();
            }
        }
        $superAttribute = [];
        foreach ($superData as $datas) {
            $data = urldecode($datas);
            $code = substr($data, 0, strpos($data, '-'));
            $value = substr($data, strpos($data, '-') + 1);
            $value = str_replace('~', ' ', $value);
            if (array_key_exists($code, $map) && array_key_exists($map[$code], $map2)) {
                $superAttribute[$map[$code]] = $map2[$map[$code]][$value];
            }
        }
        $child = $this->configurableData->getProductByAttributes($superAttribute, $product);
        return $child;
    }
}
