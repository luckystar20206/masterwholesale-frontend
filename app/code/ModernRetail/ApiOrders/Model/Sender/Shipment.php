<?php
namespace ModernRetail\ApiOrders\Model\Sender;

class  Shipment  extends \ModernRetail\ApiOrders\Model\Sender\AbstractSender{

    public $entityName = "Shipment";
    protected $_apiPath = "client/order/:id/shipment";

    public function buildRequest($shipment){



        $shipmentData = [
            'id'=>$shipment->getId(),
            'shipment_number'=>$shipment->getIncrementId(),
            'shipping_method'=>'flat',
            'created_date'=>$shipment->getCreatedAt(),
            'status'=>'shipped',
        ];

        if ($shipment->getOrder() && $shipment->getOrder()->getShippingDescription()){
            $shipmentData['shipping_method'] = $shipment->getOrder()->getShippingDescription();
        }

        foreach ($shipment->getTracksCollection() as $track){
            $shipmentData['tracking_number'] = $track->getTrackNumber();
        }


        $items =[];

        foreach($shipment->getAllItems() as $item) {
            if (!$item->getOrderItem()) continue;
            if ( in_array($item->getOrderItem()->getProduct()->getTypeId(),['bundle'])){
                continue;
            }

            $items[] = [
                'id' => $item->getId(),
                'order_item_id' => $item->getOrderItemId(),
                'qty' => intval($item->getQty()),
            ];


        }
        if(count($items)==0){
            return false;
        }

        $shipmentData['items'] = $items;
        return $shipmentData;

    }

}