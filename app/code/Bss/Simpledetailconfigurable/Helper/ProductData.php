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

class ProductData extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $productInfo;

    private $stockRegistry;

    private $configurableData;

    private $imageBuilder;

    private $imageHelper;

    private $productHelper;

    private $preselectKey;

    private $productEnabledModule;

    private $filterProvider;

    private $moduleConfig;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModule,
        \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory $preselectKey,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->productInfo = $productInfo;
        $this->stockRegistry = $stockRegistry;
        $this->configurableData = $configurableData;
        $this->imageBuilder = $imageBuilder;
        $this->imageHelper = $imageHelper;
        $this->productHelper = $productHelper;
        $this->preselectKey = $preselectKey;
        $this->productEnabledModule = $productEnabledModule;
        $this->filterProvider = $filterProvider;
        $this->moduleConfig = $moduleConfig;
    }

    public function getAllData($productEntityId)
    {
        $result = [];
        $product = $this->productInfo->getById($productEntityId);
        $result = $this->getDetailData($product);
        $isAjaxLoad = $this->getEnabledModuleOnProduct($productEntityId)['is_ajax_load'];
        $result['is_ajax_load'] = $isAjaxLoad;
        $result['preselect'] = $this->getSelectingDataWithConfig($productEntityId);
        $this->getDetailStock($result, false);
        $result['url'] = $this->productHelper->getProductUrl($product);
        if ($result['url'] == null) {
            $result['url'] = str_replace(' ', '-', $result['name']) . $this->moduleConfig->getSuffix();
        }

        if ($isAjaxLoad) {
            $this->getDetailPrice($product, $result);
            $result['child'] = [];
            return $result;
        }

        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        
        $parentPrice = 0;

        $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = $this->getChildDetail($simpleProduct);
            $result['child'][$simpleProduct] = $childProduct;
            $parentPrice = $childProduct['price']['finalPrice'];
        }
        foreach ($result['child'] as $rk => $ri) {
            $parentPrice = ($ri['price']['finalPrice'] < $parentPrice) ? $ri['price']['finalPrice'] : $parentPrice;
        }
        $result['price']['finalPrice'] = $parentPrice;
        return $result;
    }

    public function getChildDetail($childId)
    {
        try {
            $child = $this->productInfo->getById($childId);
            $result = $this->getDetailData($child);
            $this->getDetailStock($result);
            $this->getDetailPrice($child, $result);
            return $result;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    public function getDetailData($product)
    {
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $stockRegistry = $obj->get('Magento\CatalogInventory\Api\StockRegistryInterface');
        $stockitem = $stockRegistry->getStockItem($product->getId(),$product->getStore()->getWebsiteId());
        
        $data = [];
        $data['entity'] = $product->getId();
        $data['sku'] = $product->getSku();
        $data['name'] = $product->getName();
        $data['backorders'] = $stockitem->getBackorders();
        $data['desc'] = $this->filterProvider->getPageFilter()->filter($product->getDescription());
        $data['sdesc'] = $this->filterProvider->getPageFilter()->filter($product->getShortDescription());
        $data['meta_data']['meta_title'] = $product->getMetaTitle();
        $data['meta_data']['meta_keyword'] = $product->getMetaKeyword();
        $data['meta_data']['meta_description'] = $product->getMetaDescription();
        $data['additional_info'] = $this->getAdditionalInfo($product);
        $data['image'] = $this->getGalleryImages($product);
        if (version_compare($this->moduleConfig->getMagentoVersion(), '2.2.0', '<')) {
            $data['video'] = $this->getVideoData($product);
        }
        return $data;
    }

    public function getDetailStock(&$data, $isChild = true)
    {
        $childStock = $this->stockRegistry->getStockItem($data['entity']);
        $data['stock_number'] = $childStock->getQty();
        $data['stock_status'] = $childStock->getIsInStock();
        if ($isChild) {
            $data['minqty'] = ($childStock->getUseConfigMinSaleQty()) ? 0 : $childStock->getMinSaleQty();
            $data['maxqty'] = ($childStock->getUseConfigMaxSaleQty()) ? 0 : $childStock->getMaxSaleQty();
            $data['increment'] = ($childStock->getUseConfigQtyIncrements()) ? 0 : $childStock->getQtyIncrements();
        }
    }

    public function getDetailPrice($product, &$data)
    {
        $data['price']['oldPrice'] = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $data['price']['basePrice'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $data['price']['finalPrice'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $data['price']['tier_price'] = $this->getTierPriceData($product);
    }
    
    public function getSelectingKey($productId)
    {
        $result = [];
        $parentProduct = $this->configurableData->getChildrenIds($productId);
        $product = $this->productInfo->getById($productId);
        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        foreach ($parentProduct[0] as $simpleProduct) {
            $child = $this->productInfo->getById($simpleProduct);
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                if (!array_key_exists($attrLabel, $child->getAttributes())) {
                    continue;
                }
                $result[$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                $childRow = $child->getAttributes()[$attrLabel]->getFrontend()->getValue($child);
                $result[$attrValue->getAttributeId()]['child'][$child->getData($attrLabel)] = $childRow;
            }
        }
        return $result;
    }

    public function getSelectingData($productId)
    {
        $result = [];
        $collection = $this->preselectKey->create()
        ->getCollection()
        ->addFieldToFilter('product_id', $productId);
        foreach ($collection as $value) {
            $result[$value['attribute_key']] = $value['value_key'];
        }
        return $result;
    }

    public function getSelectingDataWithConfig($product)
    {
        $result = [];
        $result['data'] = $this->getSelectingData($product);
        if ($result['data'] != null) {
            $result['enabled'] = true;
        } else {
            $result['enabled'] = false;
        }
        return $result;
    }

    public function getEnabledModuleOnProduct($productId)
    {
        $resultObject = $this->productEnabledModule->create()->load($productId);
        if (!$resultObject->getProductId()) {
            return $this->productEnabledModule->create()->setData(['enabled' => 1, 'is_ajax_load' => 0]);
        }
        return $resultObject;
    }

    public function getPrice($productPrices, $basePrice, $customerId)
    {
        $customerPrice = [];
        $result = [];
        foreach ($productPrices as $key => $price) {
            if (($price['id'] == '32000' || $price['id'] == $customerId)) {
                if (array_key_exists($price['qty'], $customerPrice)) {
                    $customerPrice[$price['qty']]['value'] = min(
                        $customerPrice[$price['qty']]['value'],
                        $price['value']
                    );
                } else {
                    $customerPrice[$price['qty']]['qty'] = $price['qty'];
                    $customerPrice[$price['qty']]['value'] = $price['value'];
                }
            }
        }
        return $customerPrice;
    }

    public function getTierPriceData($product)
    {
        $result = [];
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $baseFinalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        if (isset($tierPricesList) && !empty($tierPricesList)) {
            foreach ($tierPricesList as $key => $tier) {
                $tierData = [];
                $tierData['qty'] = $tier['price_qty'];
                $tierData['final'] = $tier['price']->getValue();
                $tierData['value'] = $tier['price']->getValue();
                $tierData['base'] = $tier['price']->getBaseAmount();
                $tierData['final_discount'] = $tierData['final'] - $finalPrice;
                $tierData['base_discount'] = $tierData['base'] - $baseFinalPrice;
                $tierData['percent'] = (1 - $tierData['base']/$baseFinalPrice) * 100;
                $result[$tierData['qty']] = $tierData;
            }
        }
        return $result;
    }

    public function getGalleryImages($product)
    {
        $images = $product->getMediaGalleryImages();
        $imagesItems = [];
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                $image->setData(
                    'small_image_url',
                    $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $imagesItems[] = [
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => $image->getLabel(),
                    'position' => $image->getPosition(),
                    'isMain' => $product->getImage() == $image->getFile(),
                    'type' => str_replace('external-', '', $image->getMediaType()),
                    'videoUrl' => $image->getVideoUrl(),
                ];
            }
        }
//        if (empty($imagesItems)) {
//            $imagesItems[] = [
//                'thumb' => $this->imageHelper->getDefaultPlaceholderUrl('thumbnail'),
//                'img' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
//                'full' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
//                'type' => 'image',
//                'videoUrl' => null,
//                'caption' => '',
//                'position' => '0',
//                'isMain' => true,
//            ];
//        }
        return $imagesItems;
    }

    public function getVideoData($product)
    {
        $mediaGalleryData = [];
        foreach ($product->getMediaGalleryImages() as $mediaGalleryImage) {
            $mediaGalleryData[] = [
                'mediaType' => $mediaGalleryImage->getMediaType() === 'external-video'? 'video' : $mediaGalleryImage->getMediaType(),
                'videoUrl' => $mediaGalleryImage->getVideoUrl(),
                'isBase' => $product->getImage() == $mediaGalleryImage->getFile(),
            ];
        }
        return $mediaGalleryData;
    }

    public function getAdditionalInfo($product)
    {
        $result = [];
        foreach ($product->getAttributes() as $attrkey => $value) {
            if ($value->getData('is_visible_on_front')) {
                $valueData = $value->getFrontend()->getValue($product);
                if ($valueData != false && $valueData != 'No' && $valueData != 'N/A') {
                    $result[$attrkey]['value'] = $valueData;
                    $result[$attrkey]['label'] = $value->getStoreLabel();
                }
            }
        }
        return $result;
    }

    public function getProductRepository()
    {
        return $this->productInfo;
    }
}
