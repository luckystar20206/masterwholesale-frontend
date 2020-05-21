<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\ApiOrders\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassSend extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory,     \ModernRetail\ApiOrders\Model\Queue $apiOrdersQueue,\ModernRetail\ApiOrders\Helper\Data $mrApiOrdersHelper)
    {
        parent::__construct($context, $filter);
        $this->queue = $apiOrdersQueue;
        $this->collectionFactory = $collectionFactory;
        $this->mrApiOrdersHelper = $mrApiOrdersHelper;
    }

    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        $exported = 0;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        foreach ($collection->getItems() as $order) {
            try {
                $order =  $objectManager->create('Magento\Sales\Model\Order')->load($order->getId());
                $mrApiOrderHelper = $objectManager->create('\ModernRetail\ApiOrders\Helper\Data');
                $mrApiOrderHelper->sendOrder($order);


                if (!$mrApiOrderHelper->isEnabled($order->getStoreId())) {
                    $this->messageManager->addError('This order came from store which disabled in Modern Retail API Orders');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath($this->getComponentRefererUrl());
                    return $resultRedirect;
                }
                
                $queue = $this->queue->add("order",$order->getId());
                $result = $queue->send();

                /**
                 * Send Invoices
                 */

                if ($mrApiOrderHelper->isEnabled($order->getStoreId(), $mrApiOrderHelper::INVOICES_ENABLED)) {
                    $invoicesCollection = $objectManager->create('Magento\Sales\Model\Order\Invoice')->getCollection()->addFieldToFilter('order_id',$order->getId());
                    foreach($invoicesCollection as $invoice){
                        $queue = $this->queue->add("invoice",$invoice->getId());
                        $result = $queue->send();
                    }
                }

                /**
                 * Send Shipments
                 */

                if ($mrApiOrderHelper->isEnabled($order->getStoreId(), $mrApiOrderHelper::SHIPMENTS_ENABLED)) {
                    $shipmentCollections = $objectManager->create('Magento\Sales\Model\Order\Shipment')->getCollection()->addFieldToFilter('order_id',$order->getId());
                    foreach($shipmentCollections as $shipment){
                        $queue = $this->queue->add("shipment",$shipment->getId());
                        $result = $queue->send();
                    }
                }

                /**
                 * Send Creditmemos
                 */

                if ($mrApiOrderHelper->isEnabled($order->getStoreId(), $mrApiOrderHelper::CREDITMEMOS_ENABLED)) {
                    $creditMemoCollection = $objectManager->create('Magento\Sales\Model\Order\Creditmemo')->getCollection()->addFieldToFilter('order_id',$order->getId());
                    foreach($creditMemoCollection as $creditmemo){
                        $queue = $this->queue->add("creditmemo",$creditmemo->getId());
                        $result = $queue->send();
                    }
                }
            }catch (\Exception $ex){
                $this->messageManager->addError($ex->getMessage());
            }
            $exported++;
        }

        $notExported = $collection->count() - $exported;

        if ($notExported) {
            $this->messageManager->addError(__('%1 order(s) cannot be sent.', $notExported));
        }

        if ($exported) {
            $this->messageManager->addSuccess(__('We sent %1 order(s) to Modern Retail API', $exported));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
