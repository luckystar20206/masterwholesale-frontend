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

use Magento\Framework\Exception\NoSuchEntityException;

class AdditionalInfoSaving
{
    private $preselectKey;

    private $productEnabledModule;

    private $productInfo;

    private $customUrlResource;

    private $moduleConfig;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey $preselectKey,
        \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModuleFactory,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\CustomUrl $customUrlResource
    ) {
        $this->productEnabledModuleFactory = $productEnabledModuleFactory;
        $this->preselectKey = $preselectKey;
        $this->productInfo = $productInfo;
        $this->customUrlResource = $customUrlResource;
        $this->moduleConfig = $moduleConfig;
    }

    public function savePreselectKey($postData, $productId)
    {
        $this->preselectKey->deleteOldKey($productId);
        foreach ($postData['sdcp_preselect'] as $key => $value) {
            $this->preselectKey->savePreselectKey($productId, $key, $value);
        }
    }

    public function saveEnabledModuleOnProduct($productId, $data)
    {
        $this->productEnabledModuleFactory->create()->getResource()->deleteOldKey($productId);
        $this->productEnabledModuleFactory->create()->getResource()
        ->saveEnabled($productId, $data['enabled'], $data['is_ajax_load']);
        if ($data['is_ajax_load']) {
            $this->generateCustomUrl($productId);
        } else {
            $this->customUrlResource->deleteById($productId);
        }
    }

    public function generateCustomUrl($productId)
    {
        try {
            $parent = $this->productInfo->getById($productId);
            $parentUrl = $parent->getUrlKey();
            $parentAttribute = $parent->getTypeInstance()->getConfigurableAttributes($parent);
            $data = [];
            $result = [];
            $targetUrl = $parentUrl . $this->moduleConfig->getSuffix();
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
                $data[$attrKey] = [
                    'code' => $attrCode,
                    'values' => []
                ];
                foreach ($parent->getAttributes()[$attrCode]->getOptions() as $tvalue) {
                    $data[$attrKey]['values'][$tvalue->getValue()] = $tvalue->getLabel();
                }
            }

            $childIds = $parent->getTypeInstance()->getChildrenIds($productId);

            foreach ($childIds[0] as $childId) {
                $child = $this->productInfo->getById($childId);
                $childUrl = $parentUrl;
                foreach ($data as $attrKey => $attrValue) {
                    $childUrl .= '+' . $attrValue['code'] . '-' . $attrValue['values'][$child->getData($attrValue['code'])];
                }
                $childUrl = str_replace(' ', '~', $childUrl);

                $result[] = [
                    'product_id' => $productId,
                    'custom_url' => $childUrl,
                    'parent_url' => $targetUrl
                ];
            }
            $this->customUrlResource->deleteByUrl($targetUrl);
            $this->customUrlResource->updateUrl($result);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    public function updateCustomUrlData()
    {
        $productCollection = $this->productEnabledModuleFactory->create()->getCollection()->getItemsByColumnValue('is_ajax_load', 1);
        foreach ($productCollection as $key => $product) {
            $this->generateCustomUrl($product->getProductId());
        }
    }
}
