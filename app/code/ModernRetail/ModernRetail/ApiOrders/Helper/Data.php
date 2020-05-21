<?php

namespace ModernRetail\ApiOrders\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    const ORDERS_START_SHEDULE_AT = "modernretail_base/order/start_send_orders_from";
    const ORDERS_ENABLED = "modernretail_base/order/is_enabled";
    const INVOICES_ENABLED = "modernretail_base/order/is_enabled_invoice";
    const SHIPMENTS_ENABLED = "modernretail_base/order/is_enabled_shipment";
    const CREDITMEMOS_ENABLED = "modernretail_base/order/is_enabled_creditmemo";

    const ENABLED_CONFIG = [
        'order' => self::ORDERS_ENABLED,
        'invoice' => self::INVOICES_ENABLED,
        'shipment' => self::SHIPMENTS_ENABLED,
        'creditmemo' => self::CREDITMEMOS_ENABLED,
    ];

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \ModernRetail\Base\Helper\Api $apiHelper,
        \ModernRetail\Base\Helper\ApiLogger $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Helper\Context $context,
        \ModernRetail\ApiOrders\Model\Queue $apiOrdersQueue
    )
    {
        $this->productloader = $_productloader;
        $this->apiHelper = $apiHelper;
        $this->logger = $logger;
        $this->_resource = $resource;
        $this->_state = $state;
        $this->_registry = $registry;
        $this->connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $this->context = $context;
        $this->apiOrdersQueue = $apiOrdersQueue;

    }


    public function _getStartScheduleAt($store_id = null)
    {
        return $this->context->getScopeConfig()->getValue(self::ORDERS_START_SHEDULE_AT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
    }

    public function isEnabled($store_id, $salesEntity = "modernretail_base/order/is_enabled")
    {
        $result = false;
        if ($salesEntity !== "modernretail_base/order/is_enabled") {
            if (!$this->context->getScopeConfig()->getValue(self::ORDERS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id)) {
                $result = true;
            }

            if ($result) {
                return false;
            }
        }

        return $this->context->getScopeConfig()->getValue($salesEntity, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
    }


    public function sendOrder($order, $markAsSent = true)
    {
        if (!$this->isEnabled($order->getStoreId())) return false;

        $ret = $this->apiOrdersQueue->add('order', $order->getId());

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $sales_order = $resource->getTableName('sales_order');
            $sales_order_item = $resource->getTableName('sales_order_item');

            $sql = " 
                update $sales_order_item 
                set qty_canceled = qty_pre_canceled
                where 
                order_id in (
                select order_id from ( 
                 select  order_id, sum((qty_pre_canceled + qty_invoiced)) as calculated, sum(qty_ordered) as qty_ordered from $sales_order_item  where
                  
                  order_id = {$order->getId()} and  
                  qty_pre_canceled > 0 and qty_canceled =0  group by order_id) as tt where tt.calculated = tt.qty_ordered
                )";
            $result = $connection->query($sql)->fetchAll();
        } catch (\Exception $ex) {

        }
        return $ret;
    }


    public function sendInvoice($invoice)
    {
        if (!$this->isEnabled($invoice->getStoreId(), self::INVOICES_ENABLED)) return false;
        $this->apiOrdersQueue->add('invoice', $invoice->getId());


//
//        if($invoice->getOrder()->getData('sent_to_mr_api') == 0) {
//
//            try {
//                $order = $this->orderFactory->load($invoice->getOrder()->getId());
//
//                $this->sendOrder($order, true);
//
//            } catch (\Exception $ex) {
//
//                $this->logger->error($ex->getMessage());
//                throw $ex;
//            }
//        }


    }


    public function sendShipment($shipment, $markAsSent = true)
    {

        if (!$this->isEnabled($shipment->getStoreId(), self::SHIPMENTS_ENABLED)) return false;

        $this->apiOrdersQueue->add('shipment', $shipment->getId());

        /*

        if($shipment->getOrder()->getData('sent_to_mr_api') == 0) {

            try {
                $order = $this->orderFactory->load($shipment->getOrder()->getId());

                $this->sendOrder($order, true);

            } catch (\Exception $ex) {

                $this->logger->error($ex->getMessage());
                throw $ex;
            }
        }*/

    }


    public function sendCreditMemo($creditMemo, $markAsSent = true)
    {

        if (!$this->isEnabled($creditMemo->getStoreId(), self::CREDITMEMOS_ENABLED)) return false;


        $this->apiOrdersQueue->add('creditmemo', $creditMemo->getId());

    }


}