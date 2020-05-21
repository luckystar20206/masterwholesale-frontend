<?php
namespace ModernRetail\CancelOrderItems\Model;
use ModernRetail\CancelOrderItems\Api\CancelItemsInterface;

class CancelItems implements CancelItemsInterface{

    public function __construct(
        \Magento\Sales\Model\Order $orderCollection,
        \Magento\Framework\App\ResourceConnection $resource,
        \ModernRetail\CancelEmails\Model\EmailSender $emailSender,
        \ModernRetail\ApiOrders\Helper\Data $mrHelper,
        \Magento\Backend\Model\Session $session
    )
    {
        $this->orderCollection = $orderCollection;
        $this->_resource = $resource;
        $this->connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->emailSender = $emailSender;
        $this->mrHelper = $mrHelper;
        $this->_session = $session;
    }

    public function execute(
        string $orderId,
        string $items
    )
    {

        try {

            $_items = json_decode($items, true);
            /*file_get_contents()*/

            $order = $this->orderCollection->load($orderId);

            $hasChanged = false;

            $lastCanceledItems =[];

            foreach ($_items as $item) {

                $orderItem = $order->getItemById($item['item_id']);

                $lineItemCancelled = $orderItem['qty_pre_canceled'];

                $qtyCanceled = $item['qty'] + $lineItemCancelled;
                if ($orderItem['qty_ordered'] >= $qtyCanceled) {
                    $orderItem->setData('qty_pre_canceled', $qtyCanceled);
                    $lastCanceledItems[$item['item_id']] = $item['qty'];
                }

                if ($lineItemCancelled!= $orderItem->getData('qty_pre_canceled')){
                    $hasChanged = true;
                }

                if ($orderItem->getQtyOrdered() != $orderItem->getQtyCanceled()) {
                    $order->setTaxAmount($order->getTaxAmount() - $orderItem->getData('tax_amount'));
                    $order->setSubtotal($order->getSubtotal() - $orderItem->getData('price'));
                    $order->setBaseSubtotal($order->getSubtotal() - $orderItem->getData('price'));
                    $order->setGrandTotal($order->getGrandTotal() - $orderItem->getData('price') - $orderItem->getData('tax_amount'));
                    $order->setBaseGrandTotal($order->getGrandTotal() - $orderItem->getData('price') - $orderItem->getData('tax_amount'));
//                    $orderItem->setQtyCanceled($item['qty']);
//                    $orderItem->save();
                    $order->save();
                }

            }

            $session = $this->getSession();

            $session->setLastCanceledOrderItem($lastCanceledItems);

            $_itemStatusOrdered = [];
            $_itemStatusCanceled = [];

            foreach ($order->getAllItems() as $item) {
                $_itemStatusOrdered[] = $item['qty_ordered'];
                $_itemStatusCanceled[] = $item['qty_pre_canceled'];
            }

            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('CancelItems:Cancel Item request');


            $itemStatusOrdered = array_sum($_itemStatusOrdered);
            $itemStatusCanceled = array_sum($_itemStatusCanceled);

            if ($itemStatusOrdered == $itemStatusCanceled) {
                $order->setStatus('canceled');

                \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Psr\Log\LoggerInterface::class)->debug('CancelItems:Need to void trans');

                try {
                    if ($order->getPayment())
                        $order->getPayment()->void(new \Magento\Framework\DataObject());
                }catch (\Exception $ex){
                    \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\Psr\Log\LoggerInterface::class)->debug($ex->getMessage());
                }

                \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Psr\Log\LoggerInterface::class)->debug('CancelItems:Voided transaction after cancelation');


            } else {
                // $sendEmail = $this->emailSender->send($order);
//                if(!$sendEmail){
//                    throw new \Exception();
//                }
            }

            /**
             *  SEND CANCEL EMAILS
             */

            $order->save();
            $job = $this->mrHelper->sendOrder($order);
            $job->send();

            if ($hasChanged===true || true===true) {
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $manager = $om->get('Magento\Framework\Event\ManagerInterface');
                $emailSender = $om->create('ModernRetail\CancelEmails\Model\EmailSender');
                $emailSender->send($order);
            }
            // END OF SEND CANCEL EMAILS

            $job = $this->mrHelper->sendOrder($order);
            $job->send();

        }catch (\Exception $e){
            $job = $this->mrHelper->sendOrder($order);
            $job->send();
//            die(var_dump($e->getMessage()));
        }
    }

    public function executeOLD(
        string $orderId,
        string $items
    )
    {
        try {
            $_items = json_decode($items, true);

            $order = $this->orderCollection->load($orderId);

            foreach ($_items as $item) {

                $orderItem = $order->getItemById($item['item_id']);

                $qtyCanceled = $item['qty'] + $orderItem['qty_canceled'];

                if ($orderItem['qty_ordered'] >= $qtyCanceled) {

                    $orderItem->setData('qty_canceled', $qtyCanceled);
                }
            }

            $_itemStatusOrdered = [];
            $_itemStatusCanceled = [];

            foreach ($order->getAllItems() as $item) {
                $_itemStatusOrdered[] = $item['qty_ordered'];
                $_itemStatusCanceled[] = $item['qty_canceled'];
            }

            $itemStatusOrdered = array_sum($_itemStatusOrdered);
            $itemStatusCanceled = array_sum($_itemStatusCanceled);

            if ($itemStatusOrdered == $itemStatusCanceled) {
                $order->setStatus('canceled');
            } else {
                $sendEmail = $this->emailSender->send($order);
                if(!$sendEmail){
                    throw new \Exception();
                }
            }


            $order->save();

            $job = $this->mrHelper->sendOrder($order);
            $job->send();

        }catch (\Exception $e){
            $job = $this->mrHelper->sendOrder($order);
            $job->send();
            //die(var_dump($e->getMessage()));
        }
    }

    protected function getSession(){
        return $this->_session;
    }
}