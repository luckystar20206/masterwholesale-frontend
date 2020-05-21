<?php
namespace ModernRetail\ApiOrders\Cron;

class Export
{

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \ModernRetail\Base\Helper\Api $mrApiHelper,
        \Magento\Sales\Model\Order $order,
        \ModernRetail\Base\Helper\ApiLogger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ModernRetail\ApiOrders\Helper\Data $mrApiOrderHelper,
        \ModernRetail\ApiOrders\Model\Queue $apiOrdersQueue
    )
    {
        $this->apiLogger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->mrApiHelper = $mrApiHelper;
        $this->order = $order;
        $this->_storeManager = $storeManager;
        $this->_mrApiOrderHelper = $mrApiOrderHelper;
        $this->queue = $apiOrdersQueue;
    }




    public function execute(){

        /*
         * Step #1. Send orders
         */
        $collection = $this->queue->getCollection();
        $collection->addFieldToFilter('status',array('scheduled'));
        $collection->addFieldToFilter('type',array('order'));
        $collection->getSelect()->limit(20);
        $collection->getSelect()->order('scheduled_at asc');

        foreach($collection as $job){
            $job->send();
        }

        /**
         * Step #2. Send everything
         */

        $collection = $this->queue->getCollection();
        $collection->addFieldToFilter('status',array('scheduled'));
        $collection->addFieldToFilter('type',array('nin'=>['order']));
        $collection->getSelect()->limit(20);
        $collection->getSelect()->order('scheduled_at asc');
        foreach($collection as $job){
            $job->send();
        }


       

    }



    public function execute2()
    {

        foreach($this->storeManager->getStores() as $store){
            if (!$this->_mrApiOrderHelper->isEnabled($store->getId()) ) continue;

            $dateFilter = $this->mrApiOrderHelper->_getStartScheduleAt($store->getId());

            if(!$dateFilter){
                $date = strtotime('-2 days');
                $dateFilter = date('Y-m-d', $date);
            }


            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('sent_to_mr_api',0);
            $collection->addFieldToFilter('store_id',$store->getId());
            $collection->addFieldToFilter('created_at', array('from' =>$dateFilter));
            $collection->getSelect()->limit(20);
            if(count($collection) > 0 ){

                foreach($collection as $_order) {

                    $order = $this->order->load($_order->getId());

                    try {
                        $this->_mrApiOrderHelper->sendOrder($order, true);
                    } catch (\Exception $ex) {
                        $this->apiLogger->error($ex->getMessage());
                        continue;
                    }
                }

            }
        }

    }
}