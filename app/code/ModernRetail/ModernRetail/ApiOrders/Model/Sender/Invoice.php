<?php
namespace ModernRetail\ApiOrders\Model\Sender;

class  Invoice  extends \ModernRetail\ApiOrders\Model\Sender\AbstractSender{


    public $entityName = "Invoice";
    protected $_apiPath = "client/order/:id/invoice";

    public function buildRequest($invoice){
        $billingData = $invoice->getBillingAddress();
        $billingAdress = $this->convertAddress($billingData);
        $shippingData = $invoice->getShippingAddress();

        if (!$shippingData)
            $shippingData = $billingData;

        $shippingAdress = $this->convertAddress($shippingData);


        $items = [];
        foreach ($invoice->getItems() as $item) {
            $product = $item->getOrderItem();
            if ($product && $product->getParentItemId()) {
                continue;
            }

            $_item = [
                'id' => $item->getProductId(),
                'order_item_id' => $item->getOrderItemId(),
                'type' => 'default',
                'sku' => $item->getSku(),
                'qty' => intval($item->getQty()),
                'price' => $item->getPrice(),
                'tax' => $item->getTaxAmount(),
                'discount' => $item->getBaseDiscountAmount(),
                'total' => $item->getRowTotal(),
            ];
            $items[] = $_item;
        }



        $totals = [
            'subtotal' => $invoice->getSubtotal(),
            'tax' => $invoice->getTaxAmount(),
            'shipping' => $invoice->getShippingAmount(),
            'giftcard' => '',
            'coupon' => ''
        ];

        $invoiceData = [
            'id' => $invoice->getId(),
            'invoice_number' => $invoice->getIncrementId(),
            'created_at' => $invoice->getCreatedAt(),
            'status' => strtolower($invoice->getStateName()),
            'billing_address' => $billingAdress,
            'shipping_address' => $shippingAdress,
            'items' => $items,
            'totals' => $totals,
            'subtotal' => $invoice->getSubtotal(),
            'shipping_cost' => $invoice->getShippingAmount(),
            'tax' => $invoice->getTaxAmount(),
            'discount' => $invoice->getDiscountAmount(),
            'grandtotal' => $invoice->getGrandTotal()
        ];
        return $invoiceData;
    }



}
