<?php

namespace ModernRetail\ApiOrders\Model\Sender;

class  Creditmemo extends \ModernRetail\ApiOrders\Model\Sender\AbstractSender
{


    public $entityName = "CreditMemo";
    protected $_apiPath = "client/order/:id/creditmemo";

    public function buildRequest($creditMemo)
    {


        $creditMemoData = [
            'id' => $creditMemo->getId(),
            'creditmemo_number' => $creditMemo->getIncrementId(),
            'shipping_method' => 'flat',
            'created_at' => $creditMemo->getCreatedAt(),
            'status' => 'pending',
            'subtotal' => $creditMemo->getData('subtotal'),
            'shipping' => $creditMemo->getData('shipping_amount'),
            'grandtotal' => $creditMemo->getData('grand_total'),
            'tax' => $creditMemo->getData('tax_amount'),

        ];

        $items = [];

        foreach ($creditMemo->getAllItems() as $item) {
            if(in_array($item->getOrderItem()->getData('product_type'),['bundle','configurable'])){
                continue;
            }

            $location_id = $item->getOrderItem()->getLocationId();


            if($item->getReturnLocation()){

                $location_id = $item->getReturnLocation();
            }

            $items[] = [
                'id' => $item->getId(),
                'order_item_id' => $item->getOrderItemId(),
                'qty' => intval($item->getQty()),
                'location_id' => $location_id,
                'total' => $item->getRowTotal(),
                'tax' => $item->getData('tax_amount')
            ];

        }

        $creditMemoData['items'] = $items;

        return $creditMemoData;

    }

}



