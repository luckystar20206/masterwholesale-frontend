<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Order;

/**
 * @method \Ess\M2ePro\Model\Order\Item getParentObject()
 */

class Item extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Ebay\AbstractModel
{
    const UNPAID_ITEM_PROCESS_NOT_OPENED = 0;
    const UNPAID_ITEM_PROCESS_OPENED     = 1;

    const DISPUTE_EXPLANATION_BUYER_HAS_NOT_PAID = 'BuyerNotPaid';
    const DISPUTE_REASON_BUYER_HAS_NOT_PAID      = 'BuyerHasNotPaid';

    //########################################

    // M2ePro\TRANSLATIONS
    // Product Import is disabled in eBay Account Settings.
    // Data obtaining for eBay Item failed. Please try again later.
    // Product for eBay Item #%id% was created in Magento Catalog.

    //########################################

    private $productBuilderFactory;

    private $productFactory;

    /** @var $channelItem \Ess\M2ePro\Model\Ebay\Item */
    private $channelItem = null;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\Magento\Product\BuilderFactory $productBuilderFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Factory $parentFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productBuilderFactory = $productBuilderFactory;
        $this->productFactory = $productFactory;
        parent::__construct(
            $parentFactory,
            $modelFactory,
            $activeRecordFactory,
            $helperFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\Ebay\Order\Item');
    }

    //########################################

    public function getProxy()
    {
        return $this->modelFactory->getObject('Ebay_Order_Item_ProxyObject', [
            'item' => $this
        ]);
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Ebay\Order
     */
    public function getEbayOrder()
    {
        return $this->getParentObject()->getOrder()->getChildObject();
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Account
     */
    public function getEbayAccount()
    {
        return $this->getEbayOrder()->getEbayAccount();
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Ebay\Item
     */
    public function getChannelItem()
    {
        if ($this->channelItem === null) {
            $this->channelItem = $this->activeRecordFactory->getObject('Ebay\Item')->getCollection()
                ->addFieldToFilter('item_id', $this->getItemId())
                ->addFieldToFilter('account_id', $this->getEbayAccount()->getId())
                ->setOrder('create_date', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
                ->getFirstItem();
        }

        return $this->channelItem->getId() !== null ? $this->channelItem : null;
    }

    //########################################

    public function getTransactionId()
    {
        return $this->getData('transaction_id');
    }

    public function getSellingManagerId()
    {
        return $this->getData('selling_manager_id');
    }

    public function getItemId()
    {
        return $this->getData('item_id');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return (float)$this->getData('price');
    }

    /**
     * @return float
     */
    public function getFinalFee()
    {
        return (float)$this->getData('final_fee');
    }

    /**
     * @return float
     */
    public function getWasteRecyclingFee()
    {
        return (float)$this->getData('waste_recycling_fee');
    }

    /**
     * @return int
     */
    public function getQtyPurchased()
    {
        return (int)$this->getData('qty_purchased');
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        if (isset($taxDetails['skip_tax']) && $taxDetails['skip_tax'] === true) {
            return 0.0;
        }

        return (float)$taxDetails['amount'];
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)$taxDetails['rate'];
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getVariationDetails()
    {
        return $this->getSettings('variation_details');
    }

    /**
     * @return bool
     */
    public function hasVariation()
    {
        return !empty($this->getVariationDetails());
    }

    /**
     * @return string
     */
    public function getVariationTitle()
    {
        $variationDetails = $this->getVariationDetails();

        return isset($variationDetails['title']) ? $variationDetails['title'] : '';
    }

    /**
     * @return string
     */
    public function getVariationSku()
    {
        $variationDetails = $this->getVariationDetails();

        return isset($variationDetails['sku']) ? $variationDetails['sku'] : '';
    }

    /**
     * @return array
     */
    public function getVariationOptions()
    {
        $variationDetails = $this->getVariationDetails();
        return isset($variationDetails['options']) ? $variationDetails['options'] : [];
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getTrackingDetails()
    {
        $trackingDetails = $this->getSettings('tracking_details');
        return is_array($trackingDetails) ? $trackingDetails : [];
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getVariationProductOptions()
    {
        $channelItem = $this->getChannelItem();
        if (empty($channelItem)) {
            return $this->getVariationChannelOptions();
        }

        foreach ($channelItem->getVariations() as $variation) {
            if ($variation['channel_options'] != $this->getVariationChannelOptions()) {
                continue;
            }

            return $variation['product_options'];
        }

        return $this->getVariationChannelOptions();
    }

    /**
     * @return array
     */
    public function getVariationChannelOptions()
    {
        return $this->getVariationOptions();
    }

    //########################################

    public function canCreateMagentoOrder()
    {
        return $this->isOrdersCreationEnabled();
    }

    public function isReservable()
    {
        return $this->isOrdersCreationEnabled();
    }

    // ---------------------------------------

    private function isOrdersCreationEnabled()
    {
        $ebayItem = $this->getChannelItem();

        if ($ebayItem !== null && !$this->getEbayAccount()->isMagentoOrdersListingsModeEnabled()) {
            return false;
        }

        if ($ebayItem === null && !$this->getEbayAccount()->isMagentoOrdersListingsOtherModeEnabled()) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @return int
     */
    public function getAssociatedStoreId()
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getChannelItem() !== null) {
            return $this->getEbayAccount()->isMagentoOrdersListingsStoreCustom()
                ? $this->getEbayAccount()->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        }
        // ---------------------------------------

        return $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
    }

    //########################################

    public function getAssociatedProductId()
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getChannelItem() !== null) {
            return $this->getChannelItem()->getProductId();
        }
        // ---------------------------------------

        // 3rd party Item
        // ---------------------------------------
        $sku = $this->getSku();
        if (strlen($this->getVariationSku()) > 0) {
            $sku = $this->getVariationSku();
        }

        if ($sku != '' && strlen($sku) <= \Ess\M2ePro\Helper\Magento\Product::SKU_MAX_LENGTH) {
            $product = $this->productFactory->create()
                ->setStoreId($this->getEbayOrder()->getAssociatedStoreId())
                ->getCollection()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToFilter('sku', $sku)
                    ->getFirstItem();

            if ($product->getId()) {
                $this->associateWithProduct($product);
                return $product->getId();
            }
        }
        // ---------------------------------------

        $product = $this->createProduct();
        $this->associateWithProduct($product);

        return $product->getId();
    }

    public function prepareMagentoOptions($options)
    {
        return $this->getHelper('Component\Ebay')->prepareOptionsForOrders($options);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \Ess\M2ePro\Model\Exception
     */
    private function createProduct()
    {
        if (!$this->getEbayAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new \Ess\M2ePro\Model\Exception('Product Import is disabled in Account Settings.');
        }

        $order = $this->getParentObject()->getOrder();

        /** @var $itemImporter \Ess\M2ePro\Model\Ebay\Order\Item\Importer */
        $itemImporter = $this->modelFactory->getObject('Ebay_Order_Item_Importer', [
            'item' => $this
        ]);

        $rawItemData = $itemImporter->getDataFromChannel();

        if (empty($rawItemData)) {
            throw new \Ess\M2ePro\Model\Exception('Data obtaining for eBay Item failed. Please try again later.');
        }

        $productData = $itemImporter->prepareDataForProductCreation($rawItemData);

        // Try to find exist product with sku from eBay
        // ---------------------------------------
        $product = $this->productFactory->create()
            ->setStoreId($this->getEbayOrder()->getAssociatedStoreId())
            ->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToFilter('sku', $productData['sku'])
                ->getFirstItem();

        if ($product->getId()) {
            return $product;
        }
        // ---------------------------------------

        $storeId = $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
        if ($storeId == 0) {
            $storeId = $this->getHelper('Magento\Store')->getDefaultStoreId();
        }

        $productData['store_id'] = $storeId;
        $productData['tax_class_id'] = $this->getEbayAccount()->getMagentoOrdersListingsOtherProductTaxClassId();

        // Create product in magento
        // ---------------------------------------
        /** @var $productBuilder \Ess\M2ePro\Model\Magento\Product\Builder */
        $productBuilder = $this->productBuilderFactory->create()->setData($productData);
        $productBuilder->buildProduct();
        // ---------------------------------------

        $order->addSuccessLog(
            'Product for eBay Item #%id% was created in Magento Catalog.',
            ['!id' => $this->getItemId()]
        );

        return $productBuilder->getProduct();
    }

    private function associateWithProduct(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->hasVariation()) {
            $this->_eventManager->dispatch('ess_associate_ebay_order_item_to_product', [
                'product'    => $product,
                'order_item' => $this->getParentObject(),
            ]);
        }
    }

    //########################################

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function updateShippingStatus(array $trackingDetails = [])
    {
        if (!$this->getEbayOrder()->canUpdateShippingStatus($trackingDetails)) {
            return false;
        }

        $params = [
            'item_id' => $this->getId(),
        ];

        if (!empty($trackingDetails['carrier_code'])) {
            $trackingDetails['carrier_title'] = $this->getHelper('Component\Ebay')->getCarrierTitle(
                $trackingDetails['carrier_code'],
                isset($trackingDetails['carrier_title']) ? $trackingDetails['carrier_title'] : ''
            );
        }

        if (!empty($trackingDetails['carrier_title'])) {
            if ($trackingDetails['carrier_title'] == \Ess\M2ePro\Model\Order\Shipment\Handler::CUSTOM_CARRIER_CODE &&
                !empty($trackingDetails['shipping_method'])) {
                $trackingDetails['carrier_title'] = $trackingDetails['shipping_method'];
            }

            // remove unsupported by eBay symbols
            $trackingDetails['carrier_title'] = str_replace(
                ['\'', '"', '+', '(', ')'],
                [],
                $trackingDetails['carrier_title']
            );
        }

        $params = array_merge($params, $trackingDetails);

        $action    = \Ess\M2ePro\Model\Order\Change::ACTION_UPDATE_SHIPPING;
        $creator   = \Ess\M2ePro\Model\Order\Change::CREATOR_TYPE_OBSERVER;
        $component = \Ess\M2ePro\Helper\Component\Ebay::NICK;

        $this->activeRecordFactory->getObject('Order\Change')->create(
            $this->getParentObject()->getOrderId(),
            $action,
            $creator,
            $component,
            $params
        );

        return true;
    }

    //########################################
}
