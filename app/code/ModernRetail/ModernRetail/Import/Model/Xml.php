<?php

namespace ModernRetail\Import\Model;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\Indexer\Model\Indexer;


class Xml extends \Magento\Framework\DataObject
{
    /**
     * SimpleXML Object
     */
    private $_xml = null;
    protected $_categoryCache = array();
    private $_config = null;

    private $_reindex_codes = array(
        "catalog_product_attribute" => "Product Attributes",
        "catalog_product_price" => "Product Prices",
        //"catalog_url"=>"Catalog URL Rewrites",
        "catalog_product_flat" => "Product Flat Data",
        "catalog_category_flat" => "Category Flat Data",
        "catalog_category_product" => "Category Products",
        "catalogsearch_fulltext" => "Search Indexes",
        "cataloginventory_stock" => "Stock Statuses",
    );


    public function __construct(
        \ModernRetail\Import\Helper\Data $dataHelper,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\Category $category,
        \Magento\Eav\Model\Entity\Attribute $attribute,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Indexer $indexer,
        Status $stockStatus,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Catalog\Model\Product\Gallery\Processor $mediaGalleryProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ModernRetail\Import\Helper\AttributesCheck $attributesCheck,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\ResourceConnection $resource

    )
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $this->productModel = $productModel;
        $this->storeManager = $storeManager;
        $this->attributeModel = $attribute;
        $this->categoryModel = $category;
        $this->indexer = $indexer;
        $this->stockStatus = $stockStatus;
        $this->directoryList = $directoryList;
        $this->mediaGalleryProcessor = $mediaGalleryProcessor;
        parent::__construct(array());
        $this->setHelper($dataHelper);
        $this->setDebug(false);
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $om->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->_eventManager = $om->get('\Magento\Framework\Event\Manager');
        $this->attributesChecker = $attributesCheck;
        $this->_productRepository  = $productRepository;
        $this->_resource = $resource;
        $this->connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

    }


    public function getIdBySku($sku)
    {

    }

    private function _read()
    {
        if ($this->_xml) return $this->_xml;

        $fileName = $this->getPath() . "/" . $this->getBucket() . "/" . $this->getXmlFile();
        if (!file_exists($fileName)) throw new \Exception("FILE " . $fileName . " not found. Import Aborted");
        libxml_use_internal_errors(true);

        $xml = simplexml_load_file($fileName);

        $errorMessages = [];
        if ($xml === false) {
            // echo "Failed loading XML\n";
            foreach (libxml_get_errors() as $error) {
                $errorMessages[] = $error->message . " LINE:" . $error->line;
            }
            throw new \Exception("Failed Parse XML \n\r " . join(" \n\r\ ", $errorMessages));
        }
        $this->_xml = $xml;
        return $this->_xml;
    }


    public function downloadImage($url)
    {

        $pathes = explode("/", $url);
        $image_file = array_pop($pathes);
        $path = $this->directoryList->getPath('media');
        @mkdir($path . "/modernretail/");
        @chmod($path . "/modernretail/", 0777);
        $filePath = $path . "/modernretail/" . $image_file;

        $url = str_replace(" ", "%20", $url);
        $str = "curl -s  -o '$filePath' '$url' -H 'Pragma: no-cache' -H 'Accept-Encoding: gzip, deflate, sdch' -H 'Accept-Language: en-US,en;q=0.8' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' -H 'Cache-Control: no-cache' -H 'Connection: keep-alive' --compressed";
        system($str);


        return "/modernretail/" . $image_file;
    }

    private function _readConfig()
    {
        if ($this->_config) return $this->_config;

        $fileName = $this->getPath() . "/config.xml";

        if (!file_exists($fileName)) throw new \Exception("FILE " . $fileName . " not found. Import Aborted");
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($fileName);
        if ($xml === false) {
            echo "Failed loading XML\n";
            foreach (libxml_get_errors() as $error) {
                throw new Exception($error);
            }
        }
        $this->_config = $xml;
        return $this->_config;
    }

    /**
     * Public frontend for config
     */
    public function getConfig()
    {
        return $this->_readConfig();
    }

    public function getXml()
    {
        return $this->_read();
    }

    public function dd()
    {

        d($this->getBucket());
    }

    /**
     * run xpath Query to xml
     */
    public function xpath($query)
    {
        return $this->getXml()->xpath($query);
    }

    public function xconfig($query, $simpleType = false)
    {
        $value = $this->_readConfig()->xpath($query);
        if ($simpleType === false)
            return $value;

        $simpleTyped = (string)$value[0];
        if (is_int($simpleTyped))
            return intval($simpleTyped);

        return $simpleTyped;
    }


    public function _proccessStrings($value)
    {
        $isArray = true;
        if (!is_array($value)) {
            $isArray = false;
            $value = array($value);
        }


        foreach ($value as $k => $v) {

            $v = str_replace("|#", "&", $v);
            $v = htmlspecialchars_decode($v);

            $value[$k] = $v;
            if ($isArray === false) return $v;
        }

        return $value;
    }

    public function getAttributes($node, $key = false)
    {
        $attributes = (array)$node;

        $attributes = $attributes['@attributes'];

        $attributes = $this->_proccessStrings($attributes);

        if ($key !== false)
            return @$attributes[$key];

        return $attributes;
    }

    public function attr($node, $key)
    {
        return $this->getAttributes($node, $key);
    }


    private function _progress($value)
    {
        if (!$value) return $this;
        $lock = $this->getPath() . DS . $this->getBucket() . DS . $this->getXmlFile() . ".lock";
        file_put_contents($lock, $value . "\n\r", FILE_APPEND);
        return true;
    }

    private function _log($data)
    {

        if ($this->getDebug() === true) {
            dd($data);
        }

        if(array_key_exists('mr_import', $_SESSION)) {

            if($_SESSION['mr_import']['log_file_id']){

                if(is_string($data) && strpos(strtolower($data), 'fail') !== false || strpos(strtolower($data), 'error')  !== false){
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $logger = $objectManager->get('\ModernRetail\Import\Model\Log');
                    $file_id = $_SESSION['mr_import']['log_file_id'];
                    $logger->log(
                        [
                            'file_id' => $file_id,
                            'status' => 'failed',
                            'message' =>$data
                        ]
                    );
                }
            }
        }

        file_put_contents($this->getLogFile(), $data . "\n\r", FILE_APPEND);
        return true;
    }


    public function proccess()
    {



        /**
         * create log file
         */

        try {
            $log = $this->getPath() . DS . $this->getBucket() . DS . $this->getXmlFile() . ".log";
            touch($log);
            $this->setLogFile($log);
            file_put_contents($this->getLogFile(), "IMPORT STARTED.....\n\r");
            $this->_progress(1);


            /**
             * PERCENT
             */
            $lock = $this->getPath() . DS . $this->getBucket() . DS . $this->getXmlFile() . ".lock";
            $this->setLockFile($lock);


            switch ($this->getXml()->getName()) {

                case 'productUpdate':

                    $this->updateProducts();
                    break;
                case 'ProductImages':

                    $this->attachImages();
                    break;

                default:
                    $this->importProducts();
                    break;
            }

        }catch (\Exception $ex){
            if(array_key_exists('mr_import', $_SESSION)) {

                if($_SESSION['mr_import']['log_file_id']) {
                    $file_id = $_SESSION['mr_import']['log_file_id'];
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $logger = $objectManager->get('\ModernRetail\Import\Model\Log');
                    $logger->log(
                        [
                            'file_id' => $file_id,
                            'status' => 'failed',
                            'message' =>$ex->getMessage()
                        ]
                    );
                }
            }
        }


        /**
         * FINISHED
         */
        $this->_log("FINISH IMPORTING FROM FILE: " . $this->getBucket() . DS . $this->getXmlFile());

        $this->_eventManager->dispatch("modernretail_import_finished");

        return true;
    }


    /**
     * Attach images for exist produicts
     */
    public function attachImages()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * We nee
         */
        $cachedImagePositions = [];

        $xmlImages = $this->xpath("ProductImage");

        foreach ($xmlImages as $xmlImage) {
            $this->productModel->reset();

            $this->productModel = $objectManager->create('Magento\Catalog\Model\Product');

            $xmlImage = (array)$xmlImage;
            //$magentoProduct = $this->productModel->loadByAttribute("sku",$xmlImage['sku']);
            try {
                $magentoProduct = $this->_productRepository->get($xmlImage['sku'], true, 0);
            } catch (\Exception $ex) {
                $magentoProduct = null;
            }

            if (!$magentoProduct) {
                $this->_log("PRODUCT with integrationID = " . $xmlImage['sku'] . " not found");

                continue;
            }

            $magentoProduct = $this->productModel->load($magentoProduct->getId());
            $image = $this->downloadImage($xmlImage['ImageURL']);

            $attributes = $magentoProduct->getTypeInstance()->getSetAttributes($magentoProduct);
            $mediaGalleryAttribute = $attributes['media_gallery'];


            $position = $xmlImage['imageOrder'];

            $lowestPosition = 0;

            $thisProductImages = $this->xpath("ProductImage[sku='{$xmlImage['sku']}']/imageOrder");

            if (array_key_exists($xmlImage['sku'], $cachedImagePositions) === false) {
                foreach ($thisProductImages as $__im) {
                    $cachedImagePositions[$xmlImage['sku']][] = $__im->__toString();
                }
                sort($cachedImagePositions[$xmlImage['sku']]);
            }

            $lowestPosition = $cachedImagePositions[$xmlImage['sku']][0];

            if ($position != $lowestPosition) {
                $type = "media_image";
            } else {
                $type = array("image", "base_image", "thumbnail", "small_image");
            }

            $_fname = explode("/", $image);
            $fileName = array_pop($_fname);
            $images = $magentoProduct->getData('media_gallery')['images'];
            $needSkip = false;
            foreach ($images as $_image) {
                if (strpos($_image['file'], $fileName) !== false) {
                    $needSkip = true;
                    $this->_log("SKIPPED: IMAGE [$fileName] FOR PRODUCT with integrationID = " . $xmlImage['sku'] . " already exist");
                    continue;

                }
            }
            if ($needSkip === true) {
                continue;
            }

            $label = $xmlImage['AltText'];

            if (file_exists($this->directoryList->getPath('media') . $image)) {
                try {

                    $im = $this->mediaGalleryProcessor->addImage($magentoProduct, $this->directoryList->getPath('media') . $image, $type, false, false);
                    $this->mediaGalleryProcessor->updateImage($magentoProduct, $im, array('label' => $label, 'position' => $position, 'label_default' => $label));

                    //$magentoProduct->save();
                    $magentoProduct = $this->saveProduct($magentoProduct);
                    $this->afterProductSave($magentoProduct);
                } catch (\Exception $ex) {
                    $this->_log($ex->getMessage() . " - [$fileName] FOR PRODUCT with integrationID = " . $xmlImage['sku'] . "");
                }
            } else {
                $this->_log("SKIPPED [$fileName] not found FOR PRODUCT with integrationID = " . $xmlImage['sku'] . "");
            }

            $this->_log("FINISH ATACH IMAGE [$fileName] FOR PRODUCT with integrationID = " . $xmlImage['sku'] . "");
            $this->_log("-----------------------------------------------------");

        }
        return true;
    }


    public function updateProducts()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $products = $this->xpath("product");
        $this->_log('Found ' . count($products) . " products for update");
        $total = count($products);
        $iterator = 0;

        foreach ($products as $product) {

            $this->getHelper()->disableFlatData();

            $iterator++;

            $this->_progress((100 / $total) * $iterator);

            /**
             * Somebody made mistake in tagn name
             */

            $product = (array)$product;

            $this->productModel = $objectManager->create('Magento\Catalog\Model\Product');

            $sku = $product['integrationID'];
            if (!$sku)
                $sku = $product['integrationID'];

            if (!$sku) {
                $this->_log("INTEGRATION ID not found");
                continue;
            }

            $this->_log("UPDATE SKU:#" . $sku);
            try {
                $magentoProduct = $this->_productRepository->get($sku, true, 0);
                $this->productModel->reset();
                $magentoProduct = $this->productModel->load($magentoProduct->getId());
            } catch (\Exception $ex) {
                $magentoProduct = null;
            }

            // $this->productModel->reset();
            // $magentoProduct = $this->productModel->loadByAttribute("sku",$sku);

            if (!$magentoProduct) {
                $this->_log("PRODUCT with integrationID = " . $sku . " not found");
                continue;
            }
            //$magentoProduct = $this->productModel->load($magentoProduct->getId());

            $magentoProduct->setStoreId(0);
            $update = (array)$this->xconfig("update");

            foreach ($update as $map) {
                $map = (array)$map;
                foreach ($map as $xml_key => $attribute) {
                    if (array_key_exists($xml_key, $product)) {
                        $newValue = $product[$xml_key];

                        $hasStoreSpecified = $this->xconfig('update/'.$xml_key."[@store_specified='true']");


                        if (count($hasStoreSpecified)){
                            $magentoProduct->setStoreId($this->getHelper()->getCurrentStoreId($attribute));
                        }else {
                            $magentoProduct->setStoreId($this->getHelper()->getDefaultStoreId());
                        }

                        if ($attribute == "qty") {

                            if ($magentoProduct->getQty() <= $this->getHelper()->getMinQty() && $newValue > $this->getHelper()->getMinQty()) {
                                $magentoProduct->setData('is_in_stock', 1);
                            } else if ($magentoProduct->getQty() > $this->getHelper()->getMinQty() && $newValue <= $this->getHelper()->getMinQty()) {
                                $magentoProduct->setData('is_in_stock', 1);
                            }


                            $stockStatus = $magentoProduct->getQuantityAndStockStatus('is_in_stock');


                            $magentoProduct->setQty($newValue);

                            //$magentoProduct->setData(array("qty"=>floatval($newValue),"is_in_stock"=>1));
                            if ($newValue > 0) {
                                $stockStatus = true;
                            }


                            /**
                             * If backorders allowed
                             * we need keep it in stock
                             */
                            $obj = \Magento\Framework\App\ObjectManager::getInstance();
                            $stockRegistry = $obj->get('Magento\CatalogInventory\Api\StockRegistryInterface');
                            $stockitem = $stockRegistry->getStockItem($magentoProduct->getId(),$magentoProduct->getStore()->getWebsiteId());
                            if ($stockitem->getBackorders()){
                                $stockStatus = 1;
                            }

                            $magentoProduct->setQuantityAndStockStatus(array("qty" => floatval($newValue), 'is_in_stock' => $stockStatus));
                            $magentoProduct->setStockData(array(
                                'is_in_stock' => $stockStatus,
                                'qty' => floatval($newValue),
                                //'manage_stock' => 1,
                                //'use_config_notify_stock_qty' => 0
                            ));

                            try {
                                // CAUSING CACHE PROBLEMS $magentoProduct->save();
                                $stockItem = $this->stockRegistry->getStockItemBySku($magentoProduct->getSku());
                                $stockItem->setQty(floatval($newValue));
                                $stockItem->setIsInStock(1); // this line
                                $this->stockRegistry->updateStockItemBySku($magentoProduct->getSku(), $stockItem);
                                //$this->saveProduct($magentoProduct);
                                $this->afterProductSave($magentoProduct);
                                if ($magentoProduct->getTypeId() == 'simple') {
                                    $this->updateConfigurableStockForSimple($magentoProduct);
                                }

                            } catch (\Exception $ex) {
                                $this->_log("ERROR WHEN SAVING: {$attribute}: " . $ex->getMessage());
                            }
                            //d($magentoProduct->getStockData());

                            $this->_log("New Value for {$attribute} = $newValue ... saved");

                            continue;
                        }

                        $newValue = $this->getHelper()->getAttributeValue($attribute, $newValue);

                        if ($attribute == 'special_price') {
                            if (floatval($newValue) <= 0) {
                                $newValue = "";
                                $magentoProduct->setData('special_from_date', "");
                                $magentoProduct->setData('special_to_date', "");
                            }
                        }

                        if ($attribute == 'special_from_date' || $attribute == 'special_to_date') {
                            $date = new \DateTime($newValue);
                            if ($date->format('Y') == '1970') {
                                $newValue = "";
                            }
                        }


                        $magentoProduct->setData($attribute, $newValue);
                        $magentoProduct->getResource()->saveAttribute($magentoProduct, $attribute);


                        $this->afterProductSave($magentoProduct);

                        $this->_log("New Value for {$attribute} = $newValue ... saved");
                        /**
                         * Dispatch event when update attributes
                         */
                        $this->_eventManager->dispatch("modernretail_import_update", array("attribute" => $attribute, "product" => $magentoProduct, "value" => $newValue));
                        $this->_eventManager->dispatch("modernretail_import_update_" . $attribute, array("attribute" => $attribute, "product" => $magentoProduct, "value" => $newValue));

                    }
                }
            }

            $this->_log("FINISH UPDATE PRODUCT with integrationID = " . $sku . "");
            $this->_log("-----------------------------------------------------");
        }
    }


    public function importProducts()
    {

        /**
         * Find standalone products
         */

        $simpleProducts = $this->xpath("ProductItem");

        foreach ($simpleProducts as $product) {
            $product = (array)$product;
            $families = $this->xpath("ProductFamily/pfid[text()='{$product['pfid']}']");
            if ($families) continue;

            $this->_createSimpleProduct($product);

        }


        /**
         * Find product family
         */


        $family = $this->xpath("ProductFamily");
        $totalFamily = count($family);

        $this->_log("Found " . $totalFamily . " Products (ProductFamily)");
        $i = 0;


        foreach ($family as $_parentData) {
            $i++;
            $this->_progress((100 / $totalFamily) * $i);

            $this->_log("IMPORT Product (ProductFamily) $i \ $totalFamily");

            $this->_importConfigurable($_parentData);

        }


        return true;
    }


    private function _importConfigurable($configurable)
    {

        $configurable = (array)$configurable;
        /**
         * find simple products for this configurable
         */
        $pfid = $configurable['pfid'];

        $simpleProducts = $this->xpath("ProductItem[pfid='$pfid']");
        if ($simpleProducts === false) {
            $simpleProducts = $this->xpath("ProductItem[pfid=\"$pfid\"]");
        }

        if ($simpleProducts) {
            $simpleTotal = count($simpleProducts);
            $this->_log($simpleTotal);
            /**
             * If Product Family has only one SimpleProducts - there is simple product
             *
             * if ($simpleTotal==1){
             * return $this->_createSimpleProduct(array_shift($simpleProducts),$configurable);
             * }
             **/
            $simpleProductsData = array();

            $this->_log("---Found $simpleTotal for configurable");
            $i = 0;
            foreach ($simpleProducts as $simpleProduct) {
                $i++;
                $this->_log("--- Import $i \ $simpleTotal for configurable product");
                $d = $this->_createSimpleProductForConfigurable($simpleProduct, $configurable);

                foreach ($d as $k2 => $v2) {
                    array_push($simpleProductsData, $v2);
                }
                //array_push($simpleProductsData,$d);
            }

            /**
             * Create configurable Product
             */

            $this->_createConfigurableProduct($configurable, $simpleProductsData);

        }
    }

    /**
     * Create instance of configurable product
     *
     */
    private function _createConfigurableProduct($product, $simpleProductsData)
    {


        $websiteIds = array(1);
        $store_id = 0;

        $product = (array)$product;

        $isNew = false;

        $attributes = $this->xconfig("configurable_product_attributes/attribute");

        foreach ($attributes as $attribute) {
            $tag = $this->attr($attribute, 'tag');
            $attribute_code = $this->attr($attribute, 'attribute_code');
            $type = $this->attr($attribute, 'type');
            if (!array_key_exists($tag, $product)) continue;
            if ($type == "text") {
                $data[$attribute_code] = $product[$tag];
            } else {
                $data[$attribute_code] = $this->getHelper()->getAttributeValue($attribute_code, $product[$tag]);
            }
        }


        /**
         * FIND WEBSITE IDS
         */
        if (array_key_exists('storeview', $product) && strlen($product['storeview']) > 0) {
            $websiteIds = array();
            $storeViews = explode(",", $product['storeview']);
            foreach ($storeViews as $store_view) {
                $website = $this->getHelper()->getWebsiteByStoreCode($store_view);
                if ($website) {
                    $websiteIds[] = $website->getId();
                }
            }
        } else {
            $store_id = 0;
        }

        if ($store_id == 0) {
            $this->storeManager->setCurrentStore('admin');
        }


        $this->productModel->setStoreId($store_id)->reset();

        try {
            $cProduct = $this->_productRepository->get($data['sku'], true, $store_id);
        } catch (\Exception $ex) {
            $cProduct = null;
        }

        if ($cProduct)
            $cProduct = $cProduct->load($cProduct->getId());


        if (!$cProduct) {
            $isNew = true;

            $cProduct = $this->productModel;
            $cProduct->setStoreId($store_id);
            $cProduct
                ->setTypeId('configurable')
                ->setWebsiteIds($websiteIds)
                ->setStatus($this->xconfig('new_products_status', true))
                ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                ->setAttributeSetId(4);

        } else {
            /**
             * If product already Exist we need remove excluded to update attributes
             */
            $data = $this->getHelper()->removeExcludedAttributes($data);
            $cProduct->setStoreId($store_id);

        }

        /**
         * do not update mass statuses via CopyAttributes
         */
        $cProduct->setSkipMassStatusUpdate(true);

        if ($isNew === true || ($isNew === false && $this->getHelper()->needSkipConfigurableIfExist() === false)) {
            /**
             * add Categories
             */
            if ($this->xconfig("use_categories", true) == "1") {
                if (@$product['MRdeptName']) {
                    $ids = $this->_addCategories(@$product['MRdeptName'] . "/" . @$product['MRcatName']);
                } else {
                    $ids = $this->_addCategories(@$product['MRcatName']);
                }

                $cProduct->setCategoryIds($ids);
            }

            $data = array_merge($cProduct->getData(), $data);
            $cProduct->setData($data);
            $cProduct->setStoreId($store_id);
        }

        $cProduct->setCanSaveConfigurableAttributes(true);
        $cProduct->setCanSaveCustomOptions(true);
        $cProductTypeInstance = $cProduct->getTypeInstance();

        $_children = $cProductTypeInstance->getUsedProducts($cProduct);

//        foreach ($_children as $child){
//            dd($child->getData());
//
//        }
//       dd(count($_children));
        $attribute_ids = array();

        $usedProductIds = array();

        $alreadyUsedIds = array();
        $parentId = $cProduct->getId();


        if($this->checkAlreadyUsedIds($parentId)){
            $alreadyUsedIds = $this->checkAlreadyUsedIds($parentId);
        }

        $attributeValues = array();
        $attributesData = array();

        foreach ($simpleProductsData as $d) {
            $usedProductIds[] = $d['id'];
        }

        $usedProductIds = array_unique(array_merge($usedProductIds, $alreadyUsedIds));


        /**
         * Configurable creations depends on magento version
         */


        if (version_compare($this->getHelper()->getMagentoVersion(), '2.1.4', '<')) {

            /**
             * Only for old magento version
             */
            foreach ($simpleProductsData as $d) {

                if (!@$attributesData[$d['attribute_id']]) {
                    $attribute = $this->attributeModel->load($d['attribute_id']);
                    $attributesData[$attribute->getId()] = array(
                        'attribute_id' => $attribute->getId(),
                        'attribute_code' => $attribute->getAttributeCode(),
                        'frontend_label' => $attribute->getFrontendLabel(),
                        'values' => []
                    );

                }
                $attributesData[$d['attribute_id']]['values'][] = array(
                    'label' => $d['label'] . time(),
                    'attribute_id' => $attribute->getId(),
                    'value' => $d['value'],
                    'value_index' => $d['value']
                );
            }


            $cProduct->setAssociatedProductIds($usedProductIds);

            $cProduct->setConfigurableAttributesData($attributesData);

            $usedProductIds = array_unique($usedProductIds);
            $cProduct->setAssociatedProductIds($usedProductIds);


            // Set stock data. Yes, it needs stock data. No qty, but we need to tell it to manage stock, and that it's actually
            // in stock, else we'll end up with problems later..
            $cProduct->setStockData(array(
                'is_in_stock' => 1,
                'is_salable' => 1,
                'manage_stock' => 1,
                'use_config_notify_stock_qty' => 0
            ));

            try {


                $cProduct->setStoreId($store_id);
                //$cProduct->save();
                $cProduct = $this->saveProduct($cProduct);
                $this->afterProductSave($cProduct);


                /**
                 * fix for 2.1.4
                 */
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->resource = $objectManager->create("\Magento\Framework\App\ResourceConnection");
                $db = $this->resource->getConnection('core_write');
                $table = $this->resource->getTableName('catalog_product_super_link');
                foreach ($usedProductIds as $_id) {
                    $sql = "INSERT IGNORE INTO $table (`link_id`, `product_id`, `parent_id`) VALUES (NULL, $_id, " . $cProduct->getId() . ");";

                    $result = $db->query($sql);
                }


            } catch (\Exception $ex) {


                $this->_log("Error during create configurable product " . $cProduct->getSku() . " - " . $ex->getMessage());
            }


        } else {


            /**
             * working only for magento >= 2.1.4
             */
            $ob = \Magento\Framework\App\ObjectManager::getInstance();
            $productRepository = $ob->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
            $optionsFactory = $ob->create(\Magento\ConfigurableProduct\Helper\Product\Options\Factory::class);


            foreach ($simpleProductsData as $d) {

                if (!@$attributesData[$d['attribute_id']]) {
                    $attribute = $this->attributeModel->load($d['attribute_id']);
                    $attributesData[$attribute->getId()] = array(
                        'attribute_id' => $attribute->getId(),
                        'attribute_code' => $attribute->getAttributeCode(),
                        'frontend_label' => $attribute->getFrontendLabel(),
                        'label' => $attribute->getFrontendLabel(),
                        'values' => []
                    );

                }
                $attributesData[$d['attribute_id']]['values'][] = array(
                    'label' => $d['label'] . time(),
                    'attribute_id' => $attribute->getId(),
                    'value' => $d['value'],
                    'value_index' => $d['value']
                );
            }

            $usedProductIds = array_unique($usedProductIds);

            $cProduct->setAssociatedProductIds($usedProductIds);

            $cProduct->setConfigurableAttributesData($attributesData);
            try {
                $configurableOptions = $optionsFactory->create($attributesData);
            } catch (\Exception $e) {
                throw new \Exception('Seems like one of the configurable attributes not in magento');
            }


            $extensionConfigurableAttributes = $cProduct->getExtensionAttributes();
            $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
            $extensionConfigurableAttributes->setConfigurableProductLinks($usedProductIds);
            $cProduct->setExtensionAttributes($extensionConfigurableAttributes);


            // Set stock data. Yes, it needs stock data. No qty, but we need to tell it to manage stock, and that it's actually
            // in stock, else we'll end up with problems later..
            $cProduct->setStockData(array(
                'manage_stock' => 1,
                'use_config_notify_stock_qty' => 0,
                'is_in_stock' => 1,
                'is_salable' => 1
            ));

            try {
                $cProduct->setStoreId($store_id);

                $cProduct = $productRepository->save($cProduct);


                $this->afterProductSave($cProduct);
            } catch (\Exception $ex) {

                $this->_log("Error during create configurable product " . $cProduct->getSku() . " - " . $ex->getMessage());
            }
        }

        $this->_eventManager->dispatch("modernretail_import_product", array("product" => $cProduct));

        //Mage::dispatchEvent('mr_import_attach_images', array('product' => $cProduct ));

    }

    private function _createSimpleProduct($simple)
    {


        $simple = (array)$simple;

        $attributes = $this->xconfig("simple_product_attributes/attribute");

        $data = array();


        $configurable_attributes = array();
        foreach ($attributes as $attribute) {
            $tag = $this->attr($attribute, 'tag');
            $attribute_code = $this->attr($attribute, 'attribute_code');
            $type = $this->attr($attribute, 'type');
            if (!array_key_exists($tag, $simple)) continue;
            if ($type == "text") {
                $data[$attribute_code] = $simple[$tag];
            } else {
                $data[$attribute_code] = $this->getHelper()->getAttributeValue($attribute_code, $simple[$tag]);
                //d($data[$attribute_code]);
            }

        }
        foreach ($data as $k => $v) {
            if (is_object($v) && $v instanceof SimpleXMLElement) unset($data[$k]);
            if (is_null($v)) unset($data[$k]);
        }


        $isNew = false;
        //$data['name']= $family["name"];

        $qty = intval(@$data['qty']);
        $is_in_stock = 1;


        if ($qty <= $this->getHelper()->getMinQty() || $qty == 0) {
            $is_in_stock = 0;
        } else {
            $is_in_stock = 1;
        }


        $store_id = 0;
        $websiteIds = array(1);
        /**
         * FIND WEBSITE IDS
         */
        if (array_key_exists('storeview', $simple) && strlen($simple['storeview']) > 0) {
            $websiteIds = array();
            $storeViews = explode(",", $simple['storeview']);
            foreach ($storeViews as $store_view) {
                $website = $this->getHelper()->getWebsiteByStoreCode($store_view);
                if ($website) {
                    $websiteIds[] = $website->getId();
                }
            }
        } else {
            $store_id = 0;
        }

        if ($store_id == 0) {
            $this->storeManager->setCurrentStore('admin');
        }


        // Create the Magento product model
        $this->productModel->reset();

        //$sProduct = $this->productModel->loadByAttribute('sku', $data['sku']);
        try {
            $sProduct = $this->_productRepository->get($data['sku'], true, $store_id);
        } catch (\Exception $ex) {
            $sProduct = null;
        }
        if ($sProduct) {
            if ($sProduct->getTypeId() != 'simple') {
                $this->_log("LINE:" . __LINE__ . " - FAILED CREATE SIMPLE PRODUCT [" . $sProduct->getSku() . "]....>>> Because product already exist with different type");
                return false;
            }
            $sProduct = $this->productModel->setStoreId($store_id)->load($sProduct->getId());
        }


        if (!$sProduct) {
            $isNew = true;
            $sProduct = $this->productModel;
            $sProduct
                ->setStoreId($store_id)
                ->setTypeId($this->getHelper()->getSimpleProductType())
                ->setWebsiteIds($websiteIds)
                ->setStatus((int)$this->xconfig('new_products_status', true))
                ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                ->setAttributeSetId(4);

            // Set the stock data. Let Magento handle this as opposed to manually creating a cataloginventory/stock_item model..
            $sProduct->setQuantityAndStockStatus(array("qty" => floatval($qty), "is_in_stock" => $is_in_stock));
            $sProduct->setStockData(array(
                'is_in_stock' => $is_in_stock,
                'qty' => $qty,
                'manage_stock' => 1,
                'use_config_notify_stock_qty' => 0
            ));
            unset($data['qty']);
        } else {
            $sProduct->setStoreId($store_id);
            /**
             * If product already Exist we need remove excluded to update attributes
             */
            $data = $this->getHelper()->removeExcludedAttributes($data);
            //ci($sProduct);
            $sProduct->setQuantityAndStockStatus(array("qty" => floatval($qty), "is_in_stock" => $is_in_stock));
            $sProduct->setStockData(array(
                'is_in_stock' => $is_in_stock,
                'qty' => $qty,
                'manage_stock' => 1,
                'use_config_notify_stock_qty' => 0
            ));
        }


        if ($isNew === true || ($isNew === false && $this->getHelper()->needSkipSimpleIfExist() === false)) {
            $data = array_merge($sProduct->getData(), $data);

            $sProduct->setData($data);
            try {
                $sProduct = $this->saveProduct($sProduct);
                $this->afterProductSave($sProduct);
                $this->_eventManager->dispatch("modernretail_import_product", array("product" => $sProduct));

            } catch (\Exception $ex) {
                $this->_log("LINE:" . __LINE__ . " - FAILED CREATE SIMPLE PRODUCT [" . $sProduct->getSku() . "]....>>>" . $ex->getMessage());
            }

            //Mage::dispatchEvent('mr_import_attach_images', array('product' => $sProduct ));
        }
    }

    private function _createSimpleProductForConfigurable($simple, $parent)
    {

        $simple = (array)$simple;

        $attributes = $this->xconfig("simple_product_attributes/attribute");

        $data = array();


        $configurableForName = array();
        $configurable_attributes = array();
        foreach ($attributes as $attribute) {
            $tag = $this->attr($attribute, 'tag');
            $attribute_code = $this->attr($attribute, 'attribute_code');
            $type = $this->attr($attribute, 'type');
            if (!array_key_exists($tag, $simple)) continue;
            if ($type == "text") {
                $data[$attribute_code] = $simple[$tag];
            } else {

                $data[$attribute_code] = $this->getHelper()->getAttributeValue($attribute_code, $simple[$tag]);
            }
            /**
             * If it is configurable
             */
            if ($this->attr($attribute, "configurable")) {

                $attribute_model = $this->attributeModel;;

                $attribute_id = $attribute_model->getIdByCode('catalog_product', $attribute_code);


                $configurableForName[] = $simple[$tag];
                $configurableAttributeOptionId = $data[$attribute_code];
                $configurable_attributes[$attribute_code] = array("attribute_id" => $attribute_id, "value" => $configurableAttributeOptionId, "label" => $simple[$tag], 'frontend_label' => $attribute_code);
            }
        }

        $_attributesCodes = array_keys($configurable_attributes);


        $checkMsg = $this->attributesChecker->checkAttributes($_attributesCodes);

        if($checkMsg){
            throw new \Exception($checkMsg);
        }



        $data['name'] = $parent['name'] . " " . join(" ", $configurableForName);


        foreach ($data as $k => $v) {
            if (is_object($v) && $v instanceof SimpleXMLElement) unset($data[$k]);
            if (is_null($v)) unset($data[$k]);
        }


        $qty = intval(@$data['qty']);
        $is_in_stock = 1;


        if ($qty <= $this->getHelper()->getMinQty() || $qty == 0) {
            $is_in_stock = 0;
        } else {
            $is_in_stock = 1;
        }

        $websiteIds = array(1);
        $store_id = 0;
        /**
         * FIND WEBSITE IDS
         */
        if (array_key_exists('storeview', $parent) && strlen($parent['storeview']) > 0) {
            $websiteIds = array();
            $storeViews = explode(",", $parent['storeview']);
            foreach ($storeViews as $store_view) {
                $website = $this->getHelper()->getWebsiteByStoreCode($store_view);
                if ($website) {
                    $websiteIds[] = $website->getId();
                }
            }
        } else {
            $store_id = 0;
        }

        if ($store_id == 0) {
            $this->storeManager->setCurrentStore('admin');
        }


        // Create the Magento product model
        $this->productModel->reset();

        //$sProduct = $this->productModel->loadByAttribute('sku', $data['sku']);
        try {
            $sProduct = $this->_productRepository->get($data['sku'], true, $store_id);
        } catch (\Exception $ex) {
            $sProduct = null;
        }

        if ($sProduct) {
            $sProduct = $this->productModel->setStoreId($store_id)->load($sProduct->getId());

        }


        if (!$sProduct) {

            $sProduct = $this->productModel;

            $sProduct
                ->setStoreId($store_id)
                ->setTypeId($this->getHelper()->getSimpleProductType())
                ->setWebsiteIds($websiteIds)
                ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
                ->setAttributeSetId(4);

            // Set the stock data. Let Magento handle this as opposed to manually creating a cataloginventory/stock_item model..
            $sProduct->setStockData(array(
                'is_in_stock' => $is_in_stock,
                'qty' => $qty,
                'manage_stock' => 1,
                'use_config_notify_stock_qty' => 0
            ));
        } else {

            /**
             * If product already Exist we need remove excluded to update attributes
             */
            $data = $this->getHelper()->removeExcludedAttributes($data);
            //ci($sProduct);

            $sProduct->setStoreId($store_id);
            $sProduct->setQuantityAndStockStatus(array("qty" => floatval($qty), "is_in_stock" => $is_in_stock));
            $sProduct->setStockData(array(
                'is_in_stock' => $is_in_stock,
                'qty' => $qty,
                'manage_stock' => 1,
                'use_config_notify_stock_qty' => 0
            ));
            $sProduct->setQty($qty)->setIsInStock($is_in_stock);
            $sProduct = $this->saveProduct($sProduct);
        }


        $data = array_merge($sProduct->getData(), $data);
        $sProduct->setData($data);


        if ($this->getHelper()->needSkipSimpleIfExist() === false) {
            try {
                $sProduct = $this->saveProduct($sProduct);
                $this->afterProductSave($sProduct);

            } catch (\Exception $ex) {
                $this->_log("LINE:" . __LINE__ . "  - FAILED CREATE SIMPLE PRODUCT [" . $sProduct->getSku() . "]....>>>" . $ex->getMessage());
            }
        }


        $dataToReturn = array();


        foreach ($configurable_attributes as $code => $data) {

            $dataToReturn[] = array(
                "id" => $sProduct->getId(),
                "price" => $sProduct->getPrice(),
                "attr_code" => $code,
                "attribute_id" => $data['attribute_id'],
                "value" => $data['value'],
                "label" => $data['label']
            );
        }

        return $dataToReturn;
    }


    protected function _addCategories($categories)
    {
        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();
        $firstStore = reset($this->storeManager->getStores());

        if (!$rootId) {
            /* If stoder not create that mense admin then assign 1 to storeId */

            $storeId = $firstStore->getId();
            $rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        }

        if ($categories == "")
            return array();

        $rootPath = '1/' . $rootId;
        if (empty($this->_categoryCache[$store->getId()])) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryCollectionFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $collection = $categoryCollectionFactory->create();
            // ->setStore($store)
            $collection->addAttributeToSelect('name');
            $collection->getSelect()->where("path like '" . $rootPath . "/%'");


            foreach ($collection as $cat) {
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i = 2, $l = sizeof($pathArr); $i < $l; $i++) {
                    $_target = $collection->getItemById($pathArr[$i]);
                    if ($_target) {
                        $name = $_target->getName();
                        $namePath .= (empty($namePath) ? '' : '/') . trim($name);
                    }
                }
                $cat->setNamePath($namePath);
            }

            $cache = array();
            foreach ($collection as $cat) {
                $cache[strtolower($cat->getNamePath())] = $cat;
                $cat->unsNamePath();
            }
            $this->_categoryCache[$store->getId()] = $cache;
        }
        $cache =& $this->_categoryCache[$store->getId()];

        $catIds = array();
        foreach (explode(',', $categories) as $categoryPathStr) {
            $categoryPathStr = preg_replace('#\s*/\s*#', '/', trim($categoryPathStr));
            if (!empty($cache[$categoryPathStr])) {
                $catIds[] = $cache[$categoryPathStr]->getId();
                continue;
            }
            $path = $rootPath;
            $namePath = '';

            foreach (explode('/', $categoryPathStr) as $catName) {
                $namePath .= (empty($namePath) ? '' : '/') . strtolower($catName);
                if (empty($cache[$namePath])) {
                    $_parent_id = explode("/", $path);
                    $_parent_id = array_pop($_parent_id);
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $categoryModel = $objectManager->create('Magento\Catalog\Model\CategoryFactory')->create();
                    $cat = $categoryModel
                        ->setStoreId($store->getId())
                        ->setPath($path)
                        ->setParentId($_parent_id)
                        ->setName($catName)
                        ->setIsActive($this->scopeConfig->getValue('modernretail_import/settings/categories_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                        ->setIsAnchor(1)
                        ->setIncludeInMenu($this->scopeConfig->getValue('modernretail_import/settings/categories_include_in_menu', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                        ->setId(null)
                        ->setEntityId(null);


                    $repository = $objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
                    try {
                        $cat = $repository->save($cat);
                    } catch (\Exception $ex) {
                        $this->_log('Error while saving category ' . $catName . " [" . $cat->getUrlKey() . "] REASON:" . $ex->getMessage());
                    }

                    $cache[$namePath] = $cat;
                }
                $catId = $cache[$namePath]->getId();
                $path .= '/' . $catId;
            }
            if ($catId) {
                $catIds[] = $catId;
            }
        }
        return join(',', $catIds);
    }


    public function importAllFromBucket()
    {

        if ($handle = opendir($this->getPath() . "/" . $this->getBucket() . "/")) {
            $i = 0;
            while (false !== ($entry = readdir($handle))) {
                $i++;
                if ($entry != "." && $entry != "..") {
                    // mail("codemorgan@gmail.com","Import"."Proccess file ".$entry);
                    $this->_xml = null;
                    $this->setXmlFile($entry);



                    $this->proccess();
                }
            }
            closedir($handle);
            //mail("codemorgan@gmail.com","Import COMPLETED","Import from folder ".$this->getPath()."/".$this->getBucket()." completed");
        }
    }


    public function reindex()
    {
        $this->_log("Reindex STARTED....");
        foreach ($this->_reindex_codes as $key => $name) {
            $this->_log("- Start Reindex  -  " . $name . "...");
            try {
                $proccess = $this->indexer->getProcessByCode($key);
                if ($proccess)
                    $proccess->reindexEverything();
            } catch (Exception $ex) {
                $this->_log("-- ERROR while reindex " . $name);
                $this->_log("-- " . $ex->getMessage());
            }
            $this->_log("- Reindex " . $name . " successfull");
            $this->_log("-------------------------------------");
        }

        $this->_log("Reindex FINISHED");
        return true;
    }


    public function updateConfigurableStockForSimple($simple)
    {
        $simple_id = $simple->getId();

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $this->resource = $objectManager->create("\Magento\Framework\App\ResourceConnection");
            $db = $this->resource->getConnection('core_write');
            $catalog_product_relation = $this->resource->getTableName('catalog_product_relation');
            $cataloginventory_stock_item = $this->resource->getTableName('cataloginventory_stock_item');

            $sql = " SELECT max(is_in_stock) AS configurable_stock, parent_id as configurable_id
            FROM $catalog_product_relation
            LEFT JOIN $cataloginventory_stock_item ON $catalog_product_relation.child_id = $cataloginventory_stock_item.product_id
            WHERE parent_id =
                (SELECT parent_id
                 FROM $catalog_product_relation
                 WHERE child_id = $simple_id)";

            $result = $db->query($sql)->fetchObject();

            if (!$result) return false;

            if (!$result->configurable_stock) return false;

            $sql = "update $cataloginventory_stock_item set is_in_stock = {$result->configurable_stock} where product_id = {$result->configurable_id}";
            $db->query($sql);

            $objectManager->get('Magento\CatalogInventory\Model\Indexer\Stock')->executeRow($result->configurable_id);
        } catch (\Exception $ex) {
            $this->_log($ex->getMessage());
        }

    }


    public function afterProductSave($product)
    {

        $this->getHelper()->enableFlatData();

        $id = $product->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * reindex Price
         */
        $objectManager->get('Magento\Catalog\Model\Indexer\Product\Price')->executeRow($id);

        /**
         * reindex Stock
         */
        $objectManager->get('Magento\CatalogInventory\Model\Indexer\Stock')->executeRow($id);


        /**
         * fix catalogsearch
         */
        $this->resource = $objectManager->create("\Magento\Framework\App\ResourceConnection");
        $db = $this->resource->getConnection('core_write');
        $table = $this->resource->getTableName('catalog_product');


        /**
         * update updated_at
         */
        $catalogProductEntity = $this->resource->getTableName('catalog_product_entity');
        $results = $db->query("update $catalogProductEntity set updated_at = NOW() where entity_id = " . $id);


        $catalog_product_entity_varchar = $this->resource->getTableName('catalog_product_entity_varchar');
        $catalog_eav_attribute = $this->resource->getTableName('catalog_eav_attribute');
        $eav_attribute = $this->resource->getTableName('eav_attribute');

        $entity_id = $this->getHelper()->getEntityIdFieldName();

        $sql = "select * from $catalog_product_entity_varchar  as cpev 
		 			left join  $catalog_eav_attribute as cea on cpev.attribute_id  = cea.attribute_id
		 			left join  $eav_attribute as ea on cea.attribute_id  = ea.attribute_id
		 			where $entity_id = $id  and cea.is_searchable = 1 and ea.backend_type = 'varchar' group by store_id
		 			";
        $results = $db->query($sql)->fetchAll();

        foreach ($results as $res) {

            $website_id = $res['store_id'];

            if ($website_id == 0) $website_id = 1;
            //d(__LINE__);
            $table = $this->resource->getTableName('catalogsearch_fulltext_scope' . $website_id);
            $value = addslashes($res['value']);

            $sql = "insert ignore into $table values($id," . $res['attribute_id'] . ",'" . $value . "')";


            try {
                $db->query($sql);
            } catch (\Exception $ex) {
                $this->_log($ex->getMessage());
            }
        }
    }

    public function saveProduct($product)
    {

        if ($product->getSpecialToDate()) {
            $today = date("Y-m-d H:i:s");
            $today_time = strtotime($today);
            $expire_time = strtotime($product->getSpecialToDate());
            if ($expire_time < $today_time) {
                $product->setSpecialToDate(null);
                $product->setSpecialFromDate(null);
                $product->setSpecialPrice(null);
            }
        }
        $ret = $this->_productRepository->save($product);

        return $ret;
    }

    public function checkAlreadyUsedIds($parentId){

        try {
            $cpsl = $this->_resource->getTableName('catalog_product_super_link');

            $sql = "SELECT product_id FROM $cpsl WHERE parent_id = $parentId; ";

            $_temp = $this->connection->query($sql)->fetchAll();

            if (count($_temp) > 0) {
                $result = [];

                foreach ($_temp as $line){

                   $result[] = $line['product_id'];

                }
                return $result;


            }
        }catch (\Exception $e){

        }

        return false;

    }


}