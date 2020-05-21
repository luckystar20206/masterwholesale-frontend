<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Order\Shipment;

/**
 * Class Handler
 * @package Ess\M2ePro\Model\Amazon\Order\Shipment
 */
class Handler extends \Ess\M2ePro\Model\Order\Shipment\Handler
{
    //########################################

    /**
     * @param \Ess\M2ePro\Model\Order $order
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return int
     */
    public function handle(\Ess\M2ePro\Model\Order $order, \Magento\Sales\Model\Order\Shipment $shipment)
    {
        if (!$order->isComponentModeAmazon()) {
            throw new \InvalidArgumentException('Invalid component mode.');
        }

        $trackingDetails = $this->getTrackingDetails($order, $shipment);

        if (!$order->getChildObject()->canUpdateShippingStatus($trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = $this->getItemsToShip($order, $shipment);

        $trackingDetails['fulfillment_date'] = $shipment->getCreatedAt();

        $order->getChildObject()->updateShippingStatus($trackingDetails, $items);

        return self::HANDLE_RESULT_SUCCEEDED;
    }

    /**
     * @param \Ess\M2ePro\Model\Order          $order
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     *
     * @throws \LogicException
     *
     * @return array
     */
    private function getItemsToShip(\Ess\M2ePro\Model\Order $order, \Magento\Sales\Model\Order\Shipment $shipment)
    {
        $shipmentItems = $shipment->getAllItems();
        $orderItemDataIdentifier = \Ess\M2ePro\Helper\Data::CUSTOM_IDENTIFIER;

        $items = [];

        foreach ($shipmentItems as $shipmentItem) {
            $additionalData = $this->getHelper('Data')
                ->unserialize($shipmentItem->getOrderItem()->getAdditionalData());

            if (!isset($additionalData[$orderItemDataIdentifier]['items'])) {
                continue;
            }

            if (!is_array($additionalData[$orderItemDataIdentifier]['items'])) {
                continue;
            }

            $qtyAvailable = (int)$shipmentItem->getQty();

            foreach ($additionalData[$orderItemDataIdentifier]['items'] as $data) {
                if ($qtyAvailable <= 0) {
                    continue;
                }

                if (!isset($data['order_item_id'])) {
                    continue;
                }

                /** @var \Ess\M2ePro\Model\Amazon\Order\Item $item */
                $item = $this->activeRecordFactory->getObjectLoaded(
                    'Amazon_Order_Item',
                    $data['order_item_id'],
                    'amazon_order_item_id'
                );

                if ($item === null) {
                    continue;
                }

                $qty = $item->getQtyPurchased();

                if ($qty > $qtyAvailable) {
                    $qty = $qtyAvailable;
                }

                $items[] = [
                    'qty' => $qty,
                    'amazon_order_item_id' => $data['order_item_id']
                ];

                $qtyAvailable -= $qty;
            }
        }

        return $items;
    }

    //########################################
}
