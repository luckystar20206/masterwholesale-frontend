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
namespace Mwi\Simpledetailconfigurable\Helper;

use Magento\Framework\Exception\NoSuchEntityException;

class ProductData extends \Bss\Simpledetailconfigurable\Helper\ProductData
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
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
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
        $this->_storeManager = $storeManager;
        $this->_stockState = $stockState;
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
        //$data['custom_stock_status'] = $product->getAttributeText('stock_status');
        $total_stock = 0;
        if($product->getTypeID() == 'configurable'){
          $productTypeInstance = $product->getTypeInstance();
          $usedProducts = $productTypeInstance->getUsedProducts($product);
          foreach ($usedProducts as $simple) {
              $productStockObj = $obj->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($simple->getId());
              $total_stock += round($productStockObj->getData('qty'));
          }
        }
        /*elseif($product->hasOptions()) {
            foreach ($product->getOptions() as $o) {
                $optionType = $o->getType();

                if ($optionType == 'drop_down' || $optionType == 'radio') {
                    $values = $o->getValues();
                    foreach ($values as $k => $v) {
                        $total_stock += ($v->getData('customoptions_qty') > 0) ? $v->getData('customoptions_qty') : 0;
                    }
                }
            }
        }*/
        else {
            $productStockObj = $obj->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
            $total_stock = round($productStockObj->getData('qty'));
        }

        // print_r($product->debug());die;
        $date_available = strtotime($product->getData('date_available'));
        $date_avail_formatted = date('m/d',$date_available);
        $today = time();

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $total_stock = $stockItem->getQty();

        if($product->getTypeID() == 'configurable'){
            //out of stock, no backorder
            if($total_stock == 0 && $this->getStockIndex($product->getStockStatus()) == 'stocked' && ($date_available == NULL || $date_available <= $today) ){
               $total_stock = "On order. Item will ship when back in stock";
            //out of stock, normal stocking product with future backorder date
            } elseif ($total_stock == 0 && $this->getStockIndex($product->getStockStatus()) == 'stocked' && ($date_available > $today)){
                $total_stock = 'Item on backorder until '.$date_avail_formatted.'.  Orders placed will ship when item is back in stock.';
            //out of stock in store but ships from factory
            } elseif($total_stock == 0 && $this->getStockIndex($product->getStockStatus()) != 'stocked'){
                //If future backorder date
                if($date_available > $today){
                    $total_stock = "On backorder until ". $date_available." Will ship when item is available again.";
                } else {
                    //factory direct regular item not on back order
                    $total_stock = $product->getAttributeText('stock_status');
                }
            } else {
                //catch all to print stock status
                $total_stock = $product->getAttributeText('stock_status');
            }
        } elseif ($total_stock > 0){ //visible simple product in stock
            $total_stock = $product->getAttributeText('stock_status') ." (".intval($total_stock)." available in store).";
        } else { //visible simple product, 0 qty
            if($total_stock == 0 && $date_available > $today){ //future backorder date
                $total_stock = 'On backorder until '.$date_avail_formatted.'. Will ship when item is available again';
            } elseif($total_stock == 0 && $this->getStockIndex($product->getStockStatus()) == 'stocked' && ($date_available == NULL || $date_available <= $today)){  //stocked item with 0 stock and no backorder date
                $total_stock = 'On order. This item will ship when back in stock';
            } else { //factory direct, no future backorder date so just show stock_status
                $total_stock = $product->getAttributeText('stock_status');
            }
        }

        $total_stock = $total_stock .' <a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'stock-status" target="_blank">(More Info)</a>';

        // if($total_stock > 0 && $product->getStockStatus() == 37355){
        //     if($total_stock > 5){
        //         $stock_num = 'More than 5';
        //     } else {
        //         $stock_num = intval($total_stock);
        //     }
        //     $total_stock = '<link itemprop="availability" href="http://schema.org/InStock"/>In Stock</span> <a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'stock-status" target="_blank">(More Info)</a>';
        // } else {
        //     if($product->getStockStatus() == 37355){
        //         $total_stock = 'On Order<a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'stock-status" target="_blank">(More Info)</a>';
        //    } else {

        //         if($total_stock > 0 && $product->getStockStatus() == 37358){
        //             $stock_num = ($total_stock > 5) ? 'More than 5' : intval($total_stock);
        //             $total_stock = 'Ships from Factory <br />' .$stock_num . ' Available In Store'.' <a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'stock-status" target="_blank">(More Info)</a>';
        //         } elseif($total_stock > 0 && $product->getStockStatus() == 37356) {
        //             $stock_num = ($total_stock > 5) ? 'More than 5' : intval($total_stock);
        //             $total_stock = 'Ships from Factory (2-3 days) <br />' .$stock_num . ' Available In Store'.' <a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'stock-status" target="_blank">(More Info)</a>';
        //         } else {
        //             $stock_num = ($total_stock > 5) ? 'More than 5' : intval($total_stock);
        //             $total_stock = $product->getAttributeText('stock_status') .' <a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'stock-status" target="_blank">(More Info)</a>';
        //         }
        //     }
        // }

        $data['custom_stock_status'] = $total_stock;

        $shipmsg = '';
        $free_shipping = '';
        $hazmat = '';
        $fstext = '';
        $commercial = '';
        /*
        $fs = false;
        if($product->getdata('free_over_149') == 1){
            $fs = true;
            $fstext = ' on orders over $149';
        }
        if($product->getdata('free_over_99') == 1){
            $fs = true;
            $fstext = ' on orders over $99';
        }
        if($product->getdata('free_w_tool') == 1){
            $fs = true;
            $fstext = ' on orders over $49';
        }
        if($product->getFreeShipping() == 1){
            $fs = true;
        }
        if($fs == false){
            $data['custom_shipping'] = 'Standard <a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
        } else {

            if($product->getOversize() == 1 && $fs == true){
                $commercial = ' to commercial address ';
            }
            $data['custom_shipping'] = 'Free' .$commercial .$fstext .' in Continental US <a class="shiplink-sm" href="'.$this->_storeManager->getStore()->getBaseUrl().'shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
        }
        */
        $commercial = ($product->getdata('oversize') == 1) ? ' to commercial address ' : '';
        $shipping = $product->getdata('shipperhq_shipping_group');
        switch($shipping){
           case 47403: // Free PM OK
           $shipmsg = 'Free' .$fstext .' to US addresses <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 46803: // Free
           $shipmsg = 'Free' .$commercial .$fstext .' in Continental US <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 47403: // Free PM OK
           $shipmsg = 'Free' .$fstext .' to US addresses <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 46834: // Free 49
           $shipmsg = 'Free' .$commercial .' on orders over $49 in Continental US <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 47404: // Free PM OK
           $shipmsg = 'Free on orders over $49 in US <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 47347: // Free 99
           $shipmsg = 'Free' .$commercial .' on orders over $99 in Continental US <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 47405: // Free 99 PM OK
           $shipmsg = 'Free on orders over $99 in US <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 46821: // Freight 100
           case 46818: // Freight 200
           case 46822: // Freight 250
           case 46819: // Freight 300
           case 46820: // Freight 500
           case 46829: // Freight 85
           $shipmsg = 'LTL Freight';
           break;
           case 47406: // Schluter Ok Ship MWI
           $shipmsg = 'Standard <a class="shiplink-sm" href="/shipping-policy#schluter" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 46809: // Schluter
           $shipmsg = '$29.95 Flat rate ground shipping for unlimited Schluter products in this order. Some exclusions apply.<a class="shiplink-sm" href="/shipping-policy#schluter" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           case 46833: // Schluter 200
           $shipmsg = '$200.00 Flat rate LTL shipping for unlimited Schluter products in this order. Some exclusions apply.<a class="shiplink-sm" href="/shipping-policy#schluter" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
           default: // Standard or no group selected
           $shipmsg = 'Standard <a class="shiplink-sm" href="/shipping-policy" target="_blank" title="Shipping Policy">(More Info)</a>';
           break;
        }
        $data['custom_shipping'] = $shipmsg;

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

    private function getStockIndex($stockId){

        switch($stockId){
            case 37358:
            $stockStatus = 'factory';
            break;
            case 37356:
            $stockStatus = 'factory3';
            break;
            case 37355:
            $stockStatus = 'specialorder';
            break;
            case 37359:
            $stockStatus = 'backorder';
            break;
            default:
            $stockStatus = 'stocked'; //37357
            break;
        }
        return $stockStatus;
    }
}
