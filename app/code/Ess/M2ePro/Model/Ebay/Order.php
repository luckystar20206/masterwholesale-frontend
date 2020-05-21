<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay;

/**
 * @method \Ess\M2ePro\Model\Order getParentObject()
 * @method \Ess\M2ePro\Model\ResourceModel\Ebay\Order getResource()
 */
class Order extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Ebay\AbstractModel
{
    const ORDER_STATUS_ACTIVE     = 0;
    const ORDER_STATUS_COMPLETED  = 1;
    const ORDER_STATUS_CANCELLED  = 2;
    const ORDER_STATUS_INACTIVE   = 3;

    const CHECKOUT_STATUS_INCOMPLETE = 0;
    const CHECKOUT_STATUS_COMPLETED  = 1;

    const PAYMENT_STATUS_NOT_SELECTED = 0;
    const PAYMENT_STATUS_ERROR        = 1;
    const PAYMENT_STATUS_PROCESS      = 2;
    const PAYMENT_STATUS_COMPLETED    = 3;

    const SHIPPING_STATUS_NOT_SELECTED = 0;
    const SHIPPING_STATUS_PROCESSING   = 1;
    const SHIPPING_STATUS_COMPLETED    = 2;

    //########################################

    // M2ePro\TRANSLATIONS
    // Magento Order was canceled.
    // Magento Order cannot be canceled.

    //########################################

    private $shipmentFactory;

    private $externalTransactionsCollection = null;

    private $orderSender;

    private $invoiceSender;

    private $subTotalPrice = null;

    private $grandTotalPrice = null;

    protected $shippingAddressFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\Magento\Order\ShipmentFactory $shipmentFactory,
        \Ess\M2ePro\Model\Ebay\Order\ShippingAddressFactory $shippingAddressFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
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
        $this->shipmentFactory = $shipmentFactory;
        $this->shippingAddressFactory = $shippingAddressFactory;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;

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

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\Ebay\Order');
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Ebay\Order\ProxyObject
     */
    public function getProxy()
    {
        return $this->modelFactory->getObject('Ebay_Order_ProxyObject', ['order' => $this]);
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Ebay\Account
     */
    public function getEbayAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\ResourceModel\Ebay\Order\ExternalTransaction\Collection
     */
    public function getExternalTransactionsCollection()
    {
        if ($this->externalTransactionsCollection === null) {
            $this->externalTransactionsCollection = $this->activeRecordFactory
                ->getObject('Ebay_Order_ExternalTransaction')
                ->getCollection()
                ->addFieldToFilter('order_id', $this->getData('order_id'));
        }

        return $this->externalTransactionsCollection;
    }

    /**
     * @return bool
     */
    public function hasExternalTransactions()
    {
        return $this->getExternalTransactionsCollection()->getSize() > 0;
    }

    //########################################

    public function getEbayOrderId()
    {
        return $this->getData('ebay_order_id');
    }

    public function getSellingManagerId()
    {
        return $this->getData('selling_manager_id');
    }

    // ---------------------------------------

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    public function getBuyerUserId()
    {
        return $this->getData('buyer_user_id');
    }

    public function getBuyerMessage()
    {
        return $this->getData('buyer_message');
    }

    public function getBuyerTaxId()
    {
        return $this->getData('buyer_tax_id');
    }

    // ---------------------------------------

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getFinalFee()
    {
        /** @var \Ess\M2ePro\Model\Order\Item[] $items */
        $items = $this->getParentObject()->getItemsCollection()->getItems();

        $finalFee = 0;
        foreach ($items as $item) {
            $finalFee += $item->getChildObject()->getFinalFee();
        }

        return $finalFee;
    }

    public function getPaidAmount()
    {
        return $this->getData('paid_amount');
    }

    public function getSavedAmount()
    {
        return $this->getData('saved_amount');
    }

    // ---------------------------------------

    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
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
     * @return bool
     */
    public function isShippingPriceHasTax()
    {
        if (!$this->hasTax()) {
            return false;
        }

        if ($this->isVatTax()) {
            return true;
        }

        $taxDetails = $this->getTaxDetails();
        return isset($taxDetails['includes_shipping']) ? (bool)$taxDetails['includes_shipping'] : false;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTax()
    {
        $taxDetails = $this->getTaxDetails();
        return !empty($taxDetails['rate']);
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();
        return !$taxDetails['is_vat'];
    }

    /**
     * @return bool
     */
    public function isVatTax()
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();
        return $taxDetails['is_vat'];
    }

    // ---------------------------------------

    public function getWasteRecyclingFee()
    {
        $resultFee = 0.0;

        foreach ($this->getParentObject()->getItemsCollection() as $item) {
            /** @var \Ess\M2ePro\Model\Ebay\Order\Item $ebayItem */
            $ebayItem = $item->getChildObject();

            $resultFee += $ebayItem->getWasteRecyclingFee();
        }

        return $resultFee;
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getShippingDetails()
    {
        return $this->getSettings('shipping_details');
    }

    /**
     * @return string
     */
    public function getShippingService()
    {
        $shippingDetails = $this->getShippingDetails();
        return isset($shippingDetails['service']) ? $shippingDetails['service'] : '';
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        $shippingDetails = $this->getShippingDetails();
        return isset($shippingDetails['price']) ? (float)$shippingDetails['price'] : 0.0;
    }

    /**
     * @return string
     */
    public function getShippingDate()
    {
        $shippingDetails = $this->getShippingDetails();
        return isset($shippingDetails['date']) ? $shippingDetails['date'] : '';
    }

    /**
     * @return float
     */
    public function getCashOnDeliveryCost()
    {
        $shippingDetails = $this->getShippingDetails();
        return isset($shippingDetails['cash_on_delivery_cost'])
            ? (float)$shippingDetails['cash_on_delivery_cost'] : 0.0;
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Order\ShippingAddress
     */
    public function getShippingAddress()
    {
        $shippingDetails = $this->getShippingDetails();
        $address = isset($shippingDetails['address']) ? $shippingDetails['address'] : [];

        return $this->shippingAddressFactory->create([
            'order' => $this->getParentObject()
        ])->setData($address);
    }

    /**
     * @return array
     */
    public function getShippingTrackingDetails()
    {
        /** @var \Ess\M2ePro\Model\Order\Item[] $items */
        $items = $this->getParentObject()->getItemsCollection()->getItems();

        $trackingDetails = [];
        foreach ($items as $item) {
            $trackingDetails = array_merge($trackingDetails, $item->getChildObject()->getTrackingDetails());
        }

        $existedTrackingNumbers = [];

        foreach ($trackingDetails as $key => $trackingDetail) {
            if (in_array($trackingDetail['number'], $existedTrackingNumbers)) {
                unset($trackingDetails[$key]);
                continue;
            }

            $existedTrackingNumbers[] = $trackingDetail['number'];
        }

        return $trackingDetails;
    }

    /**
     * @return array
     */
    public function getGlobalShippingDetails()
    {
        $shippingDetails = $this->getShippingDetails();

        return isset($shippingDetails['global_shipping_details'])
            ? $shippingDetails['global_shipping_details'] : [];
    }

    /**
     * @return bool
     */
    public function isUseGlobalShippingProgram()
    {
        return !empty($this->getGlobalShippingDetails());
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Order\ShippingAddress
     */
    public function getGlobalShippingWarehouseAddress()
    {
        if (!$this->isUseGlobalShippingProgram()) {
            return null;
        }

        $globalShippingData = $this->getGlobalShippingDetails();
        $warehouseAddress = is_array($globalShippingData['warehouse_address'])
            ? $globalShippingData['warehouse_address'] : [];

        return $this->shippingAddressFactory->create([
                    'order' => $this->getParentObject()
                ])->setData($warehouseAddress);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isUseClickAndCollect()
    {
        $clickEndCollectDetails = $this->getClickAndCollectDetails();
        return !empty($clickEndCollectDetails);
    }

    /**
     * @return array
     */
    public function getClickAndCollectDetails()
    {
        $shippingDetails = $this->getShippingDetails();

        return isset($shippingDetails['click_and_collect_details'])
            ? $shippingDetails['click_and_collect_details'] : [];
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isUseInStorePickup()
    {
        $inStorePickupDetails = $this->getInStorePickupDetails();
        return !empty($inStorePickupDetails);
    }

    public function getInStorePickupDetails()
    {
        $shippingDetails = $this->getShippingDetails();

        return isset($shippingDetails['in_store_pickup_details'])
            ? $shippingDetails['in_store_pickup_details'] : [];
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getPaymentDetails()
    {
        return $this->getSettings('payment_details');
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        $paymentDetails = $this->getPaymentDetails();
        return isset($paymentDetails['method']) ? $paymentDetails['method'] : '';
    }

    /**
     * @return string
     */
    public function getPaymentDate()
    {
        $paymentDetails = $this->getPaymentDetails();
        return isset($paymentDetails['date']) ? $paymentDetails['date'] : '';
    }

    // ---------------------------------------

    public function getPurchaseUpdateDate()
    {
        return $this->getData('purchase_update_date');
    }

    public function getPurchaseCreateDate()
    {
        return $this->getData('purchase_create_date');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isCheckoutCompleted()
    {
        return (int)$this->getData('checkout_status') == self::CHECKOUT_STATUS_COMPLETED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isPaymentCompleted()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isPaymentMethodNotSelected()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_NOT_SELECTED;
    }

    /**
     * @return bool
     */
    public function isPaymentInProcess()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_PROCESS;
    }

    /**
     * @return bool
     */
    public function isPaymentFailed()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_ERROR;
    }

    /**
     * @return bool
     */
    public function isPaymentStatusUnknown()
    {
        return !$this->isPaymentCompleted() &&
               !$this->isPaymentMethodNotSelected() &&
               !$this->isPaymentInProcess() &&
               !$this->isPaymentFailed();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isShippingCompleted()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isShippingMethodNotSelected()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_NOT_SELECTED;
    }

    /**
     * @return bool
     */
    public function isShippingInProcess()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isShippingStatusUnknown()
    {
        return !$this->isShippingCompleted() &&
               !$this->isShippingMethodNotSelected() &&
               !$this->isShippingInProcess();
    }

    // ---------------------------------------

    /**
     * @return float|int|null
     */
    public function getSubtotalPrice()
    {
        if ($this->subTotalPrice === null) {
            $subtotal = 0;

            foreach ($this->getParentObject()->getItemsCollection() as $item) {
                /** @var $item \Ess\M2ePro\Model\Order\Item */
                $subtotal += $item->getChildObject()->getPrice() * $item->getChildObject()->getQtyPurchased();
            }

            $this->subTotalPrice = $subtotal;
        }

        return $this->subTotalPrice;
    }

    /**
     * @return float|null
     */
    public function getGrandTotalPrice()
    {
        if ($this->grandTotalPrice === null) {
            $this->grandTotalPrice = $this->getSubtotalPrice();
            $this->grandTotalPrice += round((float)$this->getShippingPrice(), 2);
            $this->grandTotalPrice += round((float)$this->getTaxAmount(), 2);
            $this->grandTotalPrice += round((float)$this->getWasteRecyclingFee(), 2);
        }

        return $this->grandTotalPrice;
    }

    //########################################

    public function getStatusForMagentoOrder()
    {
        $status = '';
        $this->isCheckoutCompleted() && $status = $this->getEbayAccount()->getMagentoOrdersStatusNew();
        $this->isPaymentCompleted() && $status = $this->getEbayAccount()->getMagentoOrdersStatusPaid();
        $this->isShippingCompleted() && $status = $this->getEbayAccount()->getMagentoOrdersStatusShipped();

        return $status;
    }

    //########################################

    /**
     * @return int|null
     */
    public function getAssociatedStoreId()
    {
        $storeId = null;

        $channelItems = $this->getParentObject()->getChannelItems();

        if (empty($channelItems)) {
            // 3rd party order
            // ---------------------------------------
            $storeId = $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
            // ---------------------------------------
        } else {
            // M2E order
            // ---------------------------------------
            if ($this->getEbayAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getEbayAccount()->getMagentoOrdersListingsStoreId();
            } else {
                $firstChannelItem = reset($channelItems);
                $storeId = $firstChannelItem->getStoreId();
            }
            // ---------------------------------------
        }

        if ($storeId == 0) {
            $storeId = $this->getHelper('Magento\Store')->getDefaultStoreId();
        }

        return $storeId;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        $ebayAccount = $this->getEbayAccount();

        if (!$this->isCheckoutCompleted()
            && ($ebayAccount->shouldCreateMagentoOrderWhenCheckedOut()
                || $ebayAccount->shouldCreateMagentoOrderWhenCheckedOutAndPaid())
        ) {
            return false;
        }

        if (!$this->isPaymentCompleted()
            && ($ebayAccount->shouldCreateMagentoOrderWhenPaid()
                || $ebayAccount->shouldCreateMagentoOrderWhenCheckedOutAndPaid())
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isReservable()
    {
        return true;
    }

    //########################################

    public function beforeCreateMagentoOrder()
    {
        $buyerName = $this->getBuyerName();
        if (!empty($buyerName)) {
            return;
        }

        $buyerInfo = $this->getBuyerInfo();

        $shippingDetails = $this->getShippingDetails();
        $shippingDetails['address'] = $buyerInfo['address'];

        $this->getParentObject()->setData('buyer_name', $buyerInfo['name']);
        $this->getParentObject()->setSettings('shipping_details', $shippingDetails);

        $this->getParentObject()->save();
    }

    public function afterCreateMagentoOrder()
    {
        if ($this->getEbayAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            $this->orderSender->send($this->getParentObject()->getMagentoOrder());
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreatePaymentTransaction()
    {
        if (!$this->hasExternalTransactions()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function createPaymentTransactions()
    {
        if (!$this->canCreatePaymentTransaction()) {
            return null;
        }

        /** @var $proxy \Ess\M2ePro\Model\Ebay\Order\ProxyObject */
        $proxy = $this->getParentObject()->getProxy();
        $proxy->setStore($this->getParentObject()->getStore());

        foreach ($proxy->getPaymentTransactions() as $transaction) {
            try {
                /** @var $paymentTransactionBuilder \Ess\M2ePro\Model\Magento\Order\PaymentTransaction */
                $paymentTransactionBuilder = $this->modelFactory->getObject('Magento_Order_PaymentTransaction');
                $paymentTransactionBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
                $paymentTransactionBuilder->setData($transaction);
                $paymentTransactionBuilder->buildPaymentTransaction();
            } catch (\Exception $e) {
                $this->getParentObject()->addErrorLog(
                    'Payment Transaction was not created. Reason: %msg%',
                    ['msg' => $e->getMessage()]
                );
            }
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateInvoice()
    {
        if (!$this->isPaymentCompleted()) {
            return false;
        }

        if (!$this->getEbayAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    /**
     * @return \Magento\Sales\Model\Order\Invoice|null
     * @throws \Exception
     */
    public function createInvoice()
    {
        if (!$this->canCreateInvoice()) {
            return null;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        /** @var $invoiceBuilder \Ess\M2ePro\Model\Magento\Order\Invoice */
        $invoiceBuilder = $this->modelFactory->getObject('Magento_Order_Invoice');
        $invoiceBuilder->setMagentoOrder($magentoOrder);
        $invoiceBuilder->buildInvoice();

        $invoice = $invoiceBuilder->getInvoice();

        if ($this->getEbayAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $this->invoiceSender->send($invoice);
        }

        return $invoice;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateShipments()
    {
        if (!$this->isShippingCompleted()) {
            return false;
        }

        if (!$this->getEbayAccount()->isMagentoOrdersShipmentEnabled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasShipments() || !$magentoOrder->canShip()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    /**
     * @return \Magento\Sales\Model\Order\Shipment[]|null
     */
    public function createShipments()
    {
        if (!$this->canCreateShipments()) {
            return null;
        }

        /** @var $shipmentBuilder \Ess\M2ePro\Model\Magento\Order\Shipment */
        $shipmentBuilder = $this->shipmentFactory->create($this->getParentObject()->getMagentoOrder());
        $shipmentBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
        $shipmentBuilder->buildShipments();

        return $shipmentBuilder->getShipments();
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateTracks()
    {
        $trackingDetails = $this->getShippingTrackingDetails();
        if (empty($trackingDetails)) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->hasShipments()) {
            return false;
        }

        return true;
    }

    /**
     * @return array|null
     */
    public function createTracks()
    {
        if (!$this->canCreateTracks()) {
            return null;
        }

        $tracks = [];

        try {
            /** @var $trackBuilder \Ess\M2ePro\Model\Magento\Order\Shipment\Track */
            $trackBuilder = $this->modelFactory->getObject('Magento_Order_Shipment_Track');
            $trackBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
            $trackBuilder->setTrackingDetails($this->getShippingTrackingDetails());
            $trackBuilder->setSupportedCarriers($this->getHelper('Component\Ebay')->getCarriers());
            $trackBuilder->buildTracks();
            $tracks = $trackBuilder->getTracks();
        } catch (\Exception $e) {
            $this->getParentObject()->addErrorLog(
                'Tracking details were not imported. Reason: %msg%',
                ['msg' => $e->getMessage()]
            );
        }

        if (!empty($tracks)) {
            $this->getParentObject()->addSuccessLog('Tracking details were imported.');
        }

        return $tracks;
    }

    //########################################

    private function processConnector($action, array $params = [])
    {
        /** @var $dispatcher \Ess\M2ePro\Model\Ebay\Connector\Order\Dispatcher */
        $dispatcher = $this->modelFactory->getObject('Ebay_Connector_Order_Dispatcher');
        return $dispatcher->process($action, $this->getParentObject(), $params);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function canUpdatePaymentStatus()
    {
        // ebay restriction
        if (stripos($this->getPaymentMethod(), 'paisa') !== false) {
            return false;
        }

        return !$this->isPaymentCompleted() && !$this->isPaymentStatusUnknown();
    }

    /**
     * @param array $params
     * @return bool
     */
    public function updatePaymentStatus(array $params = [])
    {
        if (!$this->canUpdatePaymentStatus()) {
            return false;
        }

        $action    = \Ess\M2ePro\Model\Order\Change::ACTION_UPDATE_PAYMENT;
        $creator   = \Ess\M2ePro\Model\Order\Change::CREATOR_TYPE_OBSERVER;
        $component = \Ess\M2ePro\Helper\Component\Ebay::NICK;

        $this->activeRecordFactory->getObject('Order\Change')->create(
            $this->getId(),
            $action,
            $creator,
            $component,
            $params
        );

        return true;
    }

    // ---------------------------------------

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function canUpdateShippingStatus(array $trackingDetails = [])
    {
        if ($this->isShippingStatusUnknown()) {
            return false;
        }

        // ebay restriction
        if (stripos($this->getPaymentMethod(), 'paisa') !== false) {
            return false;
        }

        if (!$this->isShippingMethodNotSelected() && !$this->isShippingInProcess() && empty($trackingDetails)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function updateShippingStatus(array $trackingDetails = [])
    {
        if (!$this->canUpdateShippingStatus($trackingDetails)) {
            return false;
        }

        $params = [];

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
            $this->getId(),
            $action,
            $creator,
            $component,
            $params
        );

        return true;
    }

    //########################################

    private function getBuyerInfo()
    {
        /** @var \Ess\M2ePro\Model\Order\Item $firstItem */
        $firstItem = $this->getParentObject()->getItemsCollection()->getFirstItem();

        $params = [
            'item_id' => $firstItem->getChildObject()->getItemId(),
            'transaction_id' => $firstItem->getChildObject()->getTransactionId(),
        ];

        $dispatcherObj = $this->modelFactory->getObject('Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'orders',
            'get',
            'itemTransactions',
            $params,
            'buyer_info',
            null,
            $this->getParentObject()->getAccount(),
            null
        );

        $dispatcherObj->process($connectorObj);
        $buyerInfo = $connectorObj->getResponseData();

        return $buyerInfo;
    }

    //########################################

    public function delete()
    {
        $table = $this->activeRecordFactory->getObject('Ebay_Order_ExternalTransaction')->getResource()->getMainTable();
        $this->_getResource()->getConnection()->delete(
            $table,
            ['order_id = ?'=>$this->getData('order_id')]
        );

        return parent::delete();
    }

    //########################################
}
