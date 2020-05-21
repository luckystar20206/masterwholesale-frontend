<?php
namespace ModernRetail\ApiOrders\Model\Sender;

class  Order  extends \ModernRetail\ApiOrders\Model\Sender\AbstractSender{


    protected $_apiPath = "client/order";

    public function buildRequest($order){

        $orderData = [];
        $customerData = [];

        $billingData = $order->getBillingAddress();

        $billingAdress = $this->convertAddress($order->getBillingAddress());

        $shippingAdress = $this->convertAddress($order->getShippingAddress());
        if (!$order->getShippingAddress()){
            $shippingAdress = $billingAdress;
        }

        $customerData = ['is_guest' => $order->getCustomerIsGuest(),
            'email' => $order->getCustomerEmail(),
            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
            'billing_address' => $billingAdress,
            'shipping_address' => $shippingAdress
        ];



        if(!$order->getCustomerFirstname()){
            $customerData['first_name'] = $order->getBillingAddress()->getFirstname();
            $customerData['last_name'] = $order->getBillingAddress()->getLastname();
        }



        /**
         * fill Order Lines
         */
        $items = [];
 

        foreach ($order->getAllItems() as $item) {
            $_item = [];
            $product = $this->productloader->create()->load($item->getProductId());

            $image = $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

            if ($item->getParentItemId()) {
                /**
                 * Need to skip simple in parents
                 */
                continue;
            }

            $orderItemType = $item->getData('order_item_type');
            $deliveryDate = $item->getData('delivery_date');

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();



            if(class_exists('\ModernRetail\LocationBasedShipping\Helper\Data')){
                $location_id = $objectManager->get('ModernRetail\LocationBasedShipping\Helper\Data')->getOrderItemLocationId($item);
                if ($location_id) {
                    $location_id = array_shift($location_id);
                }
            }else{
                $location_id = $item->getData('location_id');
            }


            $qtyCancelled = $item->getQtyCanceled();

            if (max($item->getData('qty_pre_canceled'),$item->getQtyCanceled())){
                $qtyCancelled = $item->getData('qty_pre_canceled');
            }



            $type ='default';

            $giftcardvalue = 0;

            if ($product->getData('is_virtual_gift_card') || $product->getData('gift_card')){
                $type = 'giftcard';
                //  $giftcardvalue = $product->getPrice();
            }



            $_item = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'type' => $type,
                'sku' => $item->getSku(),
                'qty' => intval($item->getQtyOrdered()),
                'qty_cancelled' => intval($qtyCancelled + $item->getQtyRefunded()),
                'qty_shipped' => intval($item->getQtyShipped()),
                'qty_invoiced' => intval($item->getQtyInvoiced()),
                'weight' => $item->getWeight(),
                'price' => $item->getPrice(),
                'tax' => $item->getTaxAmount(),
                'discount' => $item->getBaseDiscountAmount(),
                'total' => $item->getRowTotal(),
                'image' => $image,
                'location_id' => $location_id,
                'giftcard_value' => $giftcardvalue,
                'order_item_type' => $orderItemType,
                'delivery_date' => $deliveryDate,
                'qty_invoiced' => intval($item->getData('qty_invoiced')),
                'qty_shipped' => intval($item->getData('qty_shipped'))
            ];


            if ($item->getWidth() !== null && $item->getHeight() !== null) {
                $_item["width"] = $item->getWidth();
                $_item["height"] = $item->getHeight();
            }

            $items[] = $_item;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quote = $objectManager->create('\Magento\Quote\Model\Quote')->load($order->getQuoteId());


        @$totals['loyalty']['value'] = 0;

        if ($quote && $quote->getCustomerBalanceAmountUsed()){
            @$totals['loyalty']['value'] -= $quote->getCustomerBalanceAmountUsed();
        }

        if ($quote && $quote->getRewardCurrencyAmount()){
            @$totals['loyalty']['value'] -= $quote->getRewardCurrencyAmount();
        }


        $totals = [
            'subtotal' => $order->getSubtotal(),
            'tax' => $order->getTaxAmount(),
            'shipping' => $order->getShippingAmount(),
            'coupon' => $order->getCouponCode()
        ];

        $comment = null;
        $histories = $order->getStatusHistories();
        /** @var OrderStatusHistoryInterface $caseCreationComment */
        if ($histories) {

            $histories = array_reverse($histories);
            foreach($histories as $history){
                if ($history->getComment() == null)  continue;
                if ($history->getIsAdmin() == 1)  continue;
                if (strpos($history->getComment(),'Transaction ID')) continue;
                if (strpos($history->getComment(),'Transaction ID') && $comment) break;
                $comment = $history->getComment();
            }

        }

        if(!$comment && $order->getCustomerId()){
            $comment = $order->getCustomerNote();
        }

        $status = $order->getStatus();

        $payment =  $order->getPayment();
        $pmethod = $payment->getMethodInstance()->getCode();

        /**
         *
         * For specify your custom order attributes use event: modernretail_order_attributes
         *
         * */


       $orderAttributes = $this->_eventManager->dispatch('modernretail_order_attributes', ['order' => $order]);

       $orderData = [
            'id' => $order->getId(),
            'real_number' => $order->getIncrementId(),
            'created_date' => $order->getCreatedAt(),
            'status' => $status,
            'customer' => $customerData,
            'items' => $items,
            'totals' => $totals,
            'subtotal' => $order->getSubtotal(),
            'comment' => $comment,
            'shipping_cost' => $order->getShippingAmount(),
            'shipping_method' => $order->getShippingMethod(),
            'tax' => $order->getTaxAmount(),
            'discount' => $order->getDiscountAmount(),
            'grandtotal' => $order->getGrandTotal(),
            'giftcard_value' => 0,
            'payment_method' => $pmethod
        ];

        $orderData['attributes'] = $orderAttributes;


        return $orderData;
    }

}